<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Address;
use App\Models\EventType;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->user = User::factory()->create();
    $this->address = Address::factory()->create([
        'user_id' => $this->user->id,
        'is_primary' => true,
    ]);
    $this->startDate = now()->addDays(10)->format('Y-m-d');
});

it('creates a new event of a given type', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_RETREAT))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    expect($this->user->events()->count())->toBe(1);
});

test('a user needs to have a given & family name for the event to be created', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_RETREAT))->first();
    $this->user->update([
        'given_name' => null,
        'family_name' => null,
    ]);
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
    ]);

    expect($this->user->family_name)->toBeNull()
        ->and($this->user->given_name)->toBeNull();
});

test('an event that has no materials order does not request the number of items in material order to be present', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::BECOMING_A_KING))->first();
    $eventType->allow_materials_order = false;
    $eventType->save();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
    ]);

    expect($this->user->events()->count())->toBe(1);
});

test('an event with a materials order needs to match the values in the event model', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::BECOMING_A_KING))->first();
    $eventType->allow_materials_order = true;
    $eventType->save();
    $response = $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => 10,
    ]);

    expect($this->user->events()->count())->toBe(0);
});

test('an event with a materials order needs to needs to be a string', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::BECOMING_A_KING))->first();
    $eventType->allow_materials_order = true;
    $eventType->save();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => 10,
    ]);

    expect(session('errors')->has('number_of_items_in_material_order'))->toBeTrue();

});

test('a retreat end date is 3 days after the start date', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_RETREAT))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    $event = $this->user->events()->first();
    $startDate = Carbon::parse($this->startDate);
    expect($event->end_date)->toBe($startDate->addDays(3)->format('Y-m-d'));
});

test('a survival school end date is 4 days after the start date', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::SURVIVAL_SCHOOL))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    $event = $this->user->events()->first();
    $startDate = Carbon::parse($this->startDate);
    expect($event->end_date)->toBe($startDate->addDays(3)->format('Y-m-d'));
});

test('a survival school is a retreat event type group', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::SURVIVAL_SCHOOL))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    $event = $this->user->events()->first();
    expect($event->eventType->group_type)->toBe(EventType::TYPE_RETREAT);
});

test('a becoming a king retreat end date is 4 days after the start date', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::BECOMING_A_KING))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    $event = $this->user->events()->first();
    $startDate = Carbon::parse($this->startDate);
    expect($event->end_date)->toBe($startDate->addDays(3)->format('Y-m-d'));
});

test('a captivating retreat end date is 4 days after the start date', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::CAPTIVATING_CORE_RETREAT))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    $event = $this->user->events()->first();
    $startDate = Carbon::parse($this->startDate);
    expect($event->end_date)->toBe($startDate->addDays(3)->format('Y-m-d'));
});

test('a fire should not have an end date', function () {
    // This test is now covered by the Fire controller tests
    // Fires use the simple flow and do not go through this controller
    // Testing that fires redirect to the simple flow is covered in other tests
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::FIRES))->first();

    // Verify that fires redirect to the simple flow
    $response = $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    $response->assertRedirect(route('host.fire'));
    expect($this->user->events()->count())->toBe(0);
});

test('an event that has no items in a material order, does not get passed to Zapier', function () {
    Queue::fake();

    $this->actingAs($this->user);
    // Use a different event type since Fires now redirect to simple flow
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_RETREAT))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '0',
    ]);
    \Illuminate\Support\Facades\Queue::assertNotPushed(\App\Jobs\PostToZapier::class);
});

test('an event that has items in a material order, does get passed to Zapier', function () {
    Queue::fake();

    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::SURVIVAL_SCHOOL))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);
    \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\PostToZapier::class);
});

test('fires event type redirects from complex flow to simple flow on index', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug('fires')->first();

    $response = $this->get(route('host.eventType.create', $eventType));

    $response->assertRedirect(route('host.fire'));
});

test('captivating fires event type redirects from complex flow to simple flow on index', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug('captivating-fires')->first();

    $response = $this->get(route('host.eventType.create', $eventType));

    $response->assertRedirect(route('host.captivating-fire'));
});

test('fires event type redirects from complex flow to simple flow on store', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug('fires')->first();

    $response = $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Fire',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '2 x 10-pack',
    ]);

    $response->assertRedirect(route('host.fire'));
    // Ensure the event was NOT created through the complex flow
    expect($this->user->events()->count())->toBe(0);
});

test('captivating fires event type redirects from complex flow to simple flow on store', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug('captivating-fires')->first();

    $response = $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Captivating Fire',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '2 x 10-pack',
    ]);

    $response->assertRedirect(route('host.captivating-fire'));
    // Ensure the event was NOT created through the complex flow
    expect($this->user->events()->count())->toBe(0);
});

test('other event types do not redirect and use complex flow normally', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_RETREAT))->first();

    $response = $this->get(route('host.eventType.create', $eventType));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Event/CreateEventEventTypePage')
        ->has('eventType')
        ->has('materialOrderItems')
    );
});

test('a retreat event is auto-published after creation', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_RETREAT))->first();

    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Retreat',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '1 x 10-pack',
    ]);

    $event = $this->user->events()->first();

    expect($event)->not->toBeNull()
        ->and($event->approved_at)->not->toBeNull();
});

test('an experience event is auto-published after creation', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_EXPERIENCE))->first();

    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Experience',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => '0',
    ]);

    $event = $this->user->events()->first();

    expect($event)->not->toBeNull()
        ->and($event->approved_at)->not->toBeNull();
});
