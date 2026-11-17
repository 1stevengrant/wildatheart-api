<?php

use App\Models\User;
use App\Models\Address;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use App\Enums\UserRoleEnum;
use App\Jobs\LogActivityJob;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Actions\Event\CreateApiEvent;
use App\Actions\Event\CreateWebEvent;
use Illuminate\Support\Facades\Cache;
use App\Actions\Event\CreateFireEventType;
use App\Exceptions\EventCreationException;
use App\DataTransferObjects\EventCreationData;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->host = User::factory()->create([
        'email' => 'host@wildatheart.org',
        'given_name' => 'Sam',
        'family_name' => 'Host',
    ]);

    $this->startDate = now()->addDays(10)->format('Y-m-d');
});

/**
 * @return array<string, mixed>
 */
function eventCreationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Denver Gathering',
        'city' => 'Denver',
        'zip' => '80202',
        'country' => 'US',
        'latitude' => 39.7392,
        'longitude' => -104.9903,
        'region' => 'Colorado',
        'district' => 'Denver County',
        'locality' => 'Denver',
        'neighborhood' => 'Downtown',
        'street_address' => '123 Main St',
        'is_private' => true,
        'state' => 'CO',
        'start_date' => now()->addDays(10)->format('Y-m-d'),
    ], $overrides);
}

function eventCreationData(array $overrides = []): EventCreationData
{
    return new EventCreationData(...array_merge([
        'name' => 'Denver Gathering',
        'latitude' => 39.7392,
        'longitude' => -104.9903,
        'streetAddress' => '123 Main St',
        'city' => 'Denver',
        'zip' => '80202',
        'state' => 'CO',
        'country' => 'US',
        'region' => 'Colorado',
        'district' => 'Denver County',
        'locality' => 'Denver',
        'neighborhood' => 'Downtown',
        'isPrivate' => false,
        'startDate' => now()->addDays(10)->format('Y-m-d'),
    ], $overrides));
}

function materialsEventType(array $attributes = []): EventType
{
    return EventType::factory()->create(array_merge([
        'group_type' => EventType::TYPE_RETREAT,
        'allow_materials_order' => true,
        'zapier_webhook_url' => 'https://hooks.zapier.com/test',
    ], $attributes));
}

function giveHostPrimaryAddress(User $host): Address
{
    return Address::factory()->create([
        'user_id' => $host->id,
        'is_primary' => true,
        'street_address' => '1 Mailing Way',
        'address2' => 'Unit 4',
        'city' => 'Boulder',
        'state' => 'CO',
        'zip' => '80301',
        'country' => 'US',
    ]);
}

describe('web creation', function () {
    it('persists the submitted fields, adjusts the retreat end date and approves the event', function () {
        $eventType = materialsEventType(['allow_materials_order' => false]);

        $this->actingAs($this->host)
            ->post(route('host.eventType.create.store', $eventType), eventCreationPayload([
                'frequency' => FrequencyEnum::WEEKLY->value,
                'end_date' => now()->addYear()->format('Y-m-d'),
            ]))
            ->assertRedirect(route('my.events.active', ['event' => $this->host->events()->first()]));

        $event = $this->host->events()->sole();

        expect($event->name)->toBe('Denver Gathering')
            ->and($event->event_type_id)->toBe($eventType->id)
            ->and($event->street_address)->toBe('123 Main St')
            ->and($event->city)->toBe('Denver')
            ->and($event->zip)->toBe('80202')
            ->and($event->state)->toBe('CO')
            ->and($event->country)->toBe('US')
            ->and($event->region)->toBe('Colorado')
            ->and($event->district)->toBe('Denver County')
            ->and($event->locality)->toBe('Denver')
            ->and($event->neighborhood)->toBe('Downtown')
            ->and($event->getRawOriginal('latitude'))->toEqual(39.7392)
            ->and($event->getRawOriginal('longitude'))->toEqual(-104.9903)
            ->and($event->is_private)->toBeTrue()
            ->and($event->sex)->toBe($eventType->sex)
            ->and($event->start_date->format('Y-m-d'))->toBe($this->startDate)
            ->and($event->end_date->format('Y-m-d'))->toBe(now()->addDays(13)->format('Y-m-d'))
            ->and($event->frequency)->toBeNull()
            ->and($event->approved_at)->not->toBeNull();
    });

    it('keeps the submitted end date for event types without a fixed duration', function () {
        $eventType = materialsEventType([
            'group_type' => EventType::TYPE_EXPERIENCE,
            'allow_materials_order' => false,
        ]);

        $endDate = now()->addDays(40)->format('Y-m-d');

        CreateWebEvent::run($this->host, $eventType, eventCreationData(['endDate' => $endDate]));

        expect($this->host->events()->sole()->end_date->format('Y-m-d'))->toBe($endDate);
    });

    it('rejects malformed fields it persists', function () {
        $eventType = materialsEventType(['allow_materials_order' => false]);

        $this->actingAs($this->host)
            ->post(route('host.eventType.create.store', $eventType), eventCreationPayload([
                'end_date' => 'not a date',
                'is_private' => 'sometimes',
                'zip' => str_repeat('9', 21),
            ]))
            ->assertSessionHasErrors(['end_date', 'is_private', 'zip']);

        expect($this->host->events()->count())->toBe(0);
    });

    it('blocks a second web event within ten seconds', function () {
        $eventType = materialsEventType(['allow_materials_order' => false]);

        CreateWebEvent::run($this->host, $eventType, eventCreationData());

        expect(fn () => CreateWebEvent::run($this->host, $eventType, eventCreationData()))
            ->toThrow(EventCreationException::class, 'An event was just created. Please wait a moment before creating another.');

        $this->travel(11)->seconds();

        CreateWebEvent::run($this->host, $eventType, eventCreationData());

        expect($this->host->events()->count())->toBe(2);
    });

    it('blocks a web event while another creation for the same host is in flight', function () {
        $eventType = materialsEventType(['allow_materials_order' => false]);

        $lock = Cache::lock("event-creation:{$this->host->id}", 10);
        $lock->get();

        expect(fn () => CreateWebEvent::run($this->host, $eventType, eventCreationData()))
            ->toThrow(EventCreationException::class);

        $lock->release();

        expect($this->host->events()->count())->toBe(0);
    });

    it('returns the duplicate error to the form', function () {
        $eventType = materialsEventType(['allow_materials_order' => false]);

        CreateWebEvent::run($this->host, $eventType, eventCreationData());

        $this->actingAs($this->host)
            ->post(route('host.eventType.create.store', $eventType), eventCreationPayload())
            ->assertSessionHasErrors(['duplicate' => 'An event was just created. Please wait a moment before creating another.']);
    });

    it('sends the materials order to zapier with the mailing address', function () {
        $address = giveHostPrimaryAddress($this->host);
        $eventType = materialsEventType();

        $event = CreateWebEvent::run($this->host, $eventType, eventCreationData([
            'materialsQuantity' => '1 x 10-pack',
        ]));

        expect($event->number_of_items_in_material_order)->toBe('1 x 10-pack');

        Bus::assertDispatched(PostToZapier::class, function (PostToZapier $job) use ($event, $eventType, $address): bool {
            $order = $job->dataTransferObject;

            return $order instanceof GoogleSheetDataTransferObject
                && $order->webhookUrl === 'https://hooks.zapier.com/test'
                && $order->eventId === $event->id
                && $order->eventType === $eventType->name
                && $order->email === 'host@wildatheart.org'
                && $order->quantity === '1 x 10-pack'
                && $order->first_name === 'Sam'
                && $order->last_name === 'Host'
                && $order->address_1 === $address->street_address
                && $order->address_2 === 'Unit 4'
                && $order->city === 'Boulder'
                && $order->state === 'CO'
                && $order->zip === '80301'
                && $order->country === 'US';
        });
    });

    it('does not send a zero quantity materials order', function () {
        giveHostPrimaryAddress($this->host);

        CreateWebEvent::run($this->host, materialsEventType(), eventCreationData(['materialsQuantity' => '0']));

        Bus::assertNotDispatched(PostToZapier::class);
    });

    it('creates the event without a materials order when the host has no primary address', function () {
        $event = CreateWebEvent::run($this->host, materialsEventType(), eventCreationData([
            'materialsQuantity' => '1 x 10-pack',
        ]));

        expect($event->exists)->toBeTrue()
            ->and($event->approved_at)->not->toBeNull();

        Bus::assertNotDispatched(PostToZapier::class);
    });

    it('does not send materials for event types without materials orders', function () {
        giveHostPrimaryAddress($this->host);

        CreateWebEvent::run(
            $this->host,
            materialsEventType(['allow_materials_order' => false]),
            eventCreationData(['materialsQuantity' => '1 x 10-pack']),
        );

        Bus::assertNotDispatched(PostToZapier::class);
    });
});

describe('api creation', function () {
    it('returns the created event without publishing it', function () {
        $eventType = materialsEventType(['allow_materials_order' => false]);

        mockJwtAuthentication($this->host);

        $response = $this->withHeaders(jwtAuthenticationHeaders())
            ->postJson("/api/v1/host/event-types/{$eventType->slug}/events", eventCreationPayload())
            ->assertOk();

        $event = $this->host->events()->sole();

        expect(array_keys($response->json()))->toBe(['success', 'event']);

        $response
            ->assertJsonPath('success', true)
            ->assertJsonPath('event.id', $event->id)
            ->assertJsonPath('event.user_id', $this->host->id)
            ->assertJsonPath('event.event_type_id', $eventType->id)
            ->assertJsonPath('event.name', 'Denver Gathering')
            ->assertJsonPath('event.street_address', '123 Main St')
            ->assertJsonPath('event.city', 'Denver')
            ->assertJsonPath('event.zip', '80202')
            ->assertJsonPath('event.state', 'CO')
            ->assertJsonPath('event.country', 'US')
            ->assertJsonPath('event.region', 'Colorado')
            ->assertJsonPath('event.district', 'Denver County')
            ->assertJsonPath('event.locality', 'Denver')
            ->assertJsonPath('event.neighborhood', 'Downtown')
            ->assertJsonPath('event.latitude', 39.74)
            ->assertJsonPath('event.longitude', -104.99)
            ->assertJsonPath('event.is_private', true)
            ->assertJsonPath('event.frequency', null)
            ->assertJsonPath('event.number_of_items_in_material_order', null)
            ->assertJsonPath('event.end_date', now()->addDays(13)->startOfDay()->toJSON())
            ->assertJsonPath('event.event_type.id', $eventType->id)
            ->assertJsonPath('event.user.id', $this->host->id)
            ->assertJsonMissingPath('event.approved_at');

        expect($event->approved_at)->toBeNull();
    });

    it('stores the frequency only for fire event types', function () {
        $fire = materialsEventType(['group_type' => EventType::TYPE_FIRE, 'allow_materials_order' => false]);
        $retreat = materialsEventType(['allow_materials_order' => false]);

        $fireEvent = CreateApiEvent::run($this->host, $fire, eventCreationData([
            'frequency' => FrequencyEnum::WEEKLY->value,
        ]));
        $retreatEvent = CreateApiEvent::run($this->host, $retreat, eventCreationData([
            'frequency' => FrequencyEnum::WEEKLY->value,
        ]));

        expect($fireEvent->frequency)->toBe(FrequencyEnum::WEEKLY)
            ->and($retreatEvent->frequency)->toBeNull();
    });

    it('requires a valid frequency for fire event types', function () {
        $eventType = materialsEventType(['group_type' => EventType::TYPE_FIRE, 'allow_materials_order' => false]);

        mockJwtAuthentication($this->host);

        $this->withHeaders(jwtAuthenticationHeaders())
            ->postJson("/api/v1/host/event-types/{$eventType->slug}/events", eventCreationPayload())
            ->assertJsonValidationErrors(['frequency']);

        $this->withHeaders(jwtAuthenticationHeaders())
            ->postJson("/api/v1/host/event-types/{$eventType->slug}/events", eventCreationPayload(['frequency' => 'daily']))
            ->assertJsonValidationErrors(['frequency']);

        expect($this->host->events()->count())->toBe(0);
    });

    it('does not apply the web duplicate guard', function () {
        $eventType = materialsEventType(['allow_materials_order' => false]);

        CreateApiEvent::run($this->host, $eventType, eventCreationData());
        CreateApiEvent::run($this->host, $eventType, eventCreationData());

        expect($this->host->events()->count())->toBe(2);
    });

    it('sends the materials order to zapier', function () {
        giveHostPrimaryAddress($this->host);

        $event = CreateApiEvent::run($this->host, materialsEventType(), eventCreationData([
            'materialsQuantity' => '1 x 10-pack',
        ]));

        Bus::assertDispatched(
            PostToZapier::class,
            fn (PostToZapier $job): bool => $job->dataTransferObject->eventId === $event->id
                && $job->dataTransferObject->quantity === '1 x 10-pack'
                && $job->dataTransferObject->zip === '80301',
        );
    });

    it('creates the event without a materials order when the host has no primary address', function () {
        $eventType = materialsEventType();

        mockJwtAuthentication($this->host);

        $this->withHeaders(jwtAuthenticationHeaders())
            ->postJson("/api/v1/host/event-types/{$eventType->slug}/events", eventCreationPayload([
                'number_of_items_in_material_order' => '1 x 10-pack',
            ]))
            ->assertOk();

        expect($this->host->events()->count())->toBe(1);

        Bus::assertNotDispatched(PostToZapier::class);
    });

    it('does not send a zero quantity materials order', function () {
        giveHostPrimaryAddress($this->host);

        CreateApiEvent::run($this->host, materialsEventType(), eventCreationData(['materialsQuantity' => '0']));

        Bus::assertNotDispatched(PostToZapier::class);
    });
});

describe('fire creation', function () {
    it('creates an approved fire and promotes the host', function () {
        $eventType = EventType::query()->where('name', EventType::FIRES)->sole();

        $event = CreateFireEventType::run($this->host, $eventType, eventCreationData([
            'country' => 'United States',
            'frequency' => FrequencyEnum::BIWEEKLY->value,
            'startDate' => null,
        ]));

        expect($event->user_id)->toBe($this->host->id)
            ->and($event->event_type_id)->toBe($eventType->id)
            ->and($event->country)->toBe('us')
            ->and($event->frequency)->toBe(FrequencyEnum::BIWEEKLY)
            ->and($event->start_date)->toBeNull()
            ->and($event->end_date)->toBeNull()
            ->and($event->approved_at)->not->toBeNull()
            ->and($this->host->fresh()->hasRole(UserRoleEnum::Host->value))->toBeTrue();

        Bus::assertDispatched(
            LogActivityJob::class,
            fn (LogActivityJob $job): bool => $job->description === 'Create Event'
                && $job->causedBy?->is($this->host)
                && $job->performedOn?->is($event),
        );
        Bus::assertNotDispatched(PostToZapier::class);
    });

    it('blocks a second fire within ten seconds', function () {
        $eventType = EventType::query()->where('name', EventType::FIRES)->sole();

        CreateFireEventType::run($this->host, $eventType, eventCreationData(['frequency' => 'weekly']));

        expect(fn () => CreateFireEventType::run($this->host, $eventType, eventCreationData(['frequency' => 'weekly'])))
            ->toThrow(EventCreationException::class);

        expect($this->host->events()->count())->toBe(1);
    });

    it('redirects inertia requests to the gift page', function () {
        $this->actingAs($this->host)
            ->post(route('host.captivating-fire.store'), eventCreationPayload(['frequency' => 'monthly']), ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('gift.index', ['event' => $this->host->events()->sole()]));

        expect($this->host->events()->sole()->eventType->name)->toBe(EventType::CAPTIVATING_FIRE);
    });

    it('rejects an unknown frequency', function () {
        $this->actingAs($this->host)
            ->post(route('host.fire.store'), eventCreationPayload(['frequency' => 'daily']))
            ->assertSessionHasErrors(['frequency']);

        expect($this->host->events()->count())->toBe(0);
    });
});
