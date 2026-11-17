<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\ExportEventSearchJob;

use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Storage;
use App\Data\InternalEventSearchCriteriaData;
use App\Enums\Internal\EventVisibilityFilter;
use App\Actions\Event\BuildInternalEventSearchQuery;
use Illuminate\Support\Facades\Event as EventFacade;

function internalSearchCriteria(mixed ...$overrides): InternalEventSearchCriteriaData
{
    return new InternalEventSearchCriteriaData(...array_merge([
        'lat' => null,
        'lng' => null,
        'radius' => null,
        'eventTypeIds' => [],
        'activeOnly' => false,
        'includeDeleted' => false,
        'createdFrom' => null,
        'createdTo' => null,
        'privacy' => EventVisibilityFilter::All,
    ], $overrides));
}

/** @return array<int, string> */
function internalSearchIds(InternalEventSearchCriteriaData $criteria): array
{
    return BuildInternalEventSearchQuery::run($criteria)->pluck('events.id')->sort()->values()->all();
}

it('limits results to events within the radius', function () {
    $near = Event::factory()->create(['latitude' => 51.5074, 'longitude' => -0.1278]);
    Event::factory()->create(['latitude' => 40.7128, 'longitude' => -74.0060]);
    Event::factory()->create(['latitude' => null, 'longitude' => null]);

    $criteria = internalSearchCriteria(lat: 51.5, lng: -0.12, radius: 30.0);

    expect(internalSearchIds($criteria))->toBe([$near->id]);
});

it('ignores coordinates unless lat, lng and radius are all present', function () {
    Event::factory()->count(2)->create();

    $criteria = internalSearchCriteria(lat: 51.5, lng: -0.12);

    expect(internalSearchIds($criteria))->toHaveCount(2);
});

it('limits results to published events when active only', function () {
    $published = Event::factory()->approved()->create();
    Event::factory()->create(['approved_at' => null]);

    expect(internalSearchIds(internalSearchCriteria(activeOnly: true)))->toBe([$published->id])
        ->and(internalSearchIds(internalSearchCriteria(activeOnly: false)))->toHaveCount(2);
});

it('filters by privacy', function () {
    $public = Event::factory()->create(['is_private' => false]);
    $private = Event::factory()->create(['is_private' => true]);

    expect(internalSearchIds(internalSearchCriteria(privacy: EventVisibilityFilter::Public)))->toBe([$public->id])
        ->and(internalSearchIds(internalSearchCriteria(privacy: EventVisibilityFilter::Private)))->toBe([$private->id])
        ->and(internalSearchIds(internalSearchCriteria(privacy: EventVisibilityFilter::All)))->toHaveCount(2);
});

it('includes deleted events only when requested', function () {
    $live = Event::factory()->create();
    $deleted = Event::factory()->create();
    $deleted->delete();

    expect(internalSearchIds(internalSearchCriteria()))->toBe([$live->id])
        ->and(internalSearchIds(internalSearchCriteria(includeDeleted: true)))->toHaveCount(2);
});

it('filters by event type', function () {
    $fire = Event::factory()->fires()->create();
    Event::factory()->survivalSchool()->create();

    $criteria = internalSearchCriteria(eventTypeIds: [$fire->event_type_id]);

    expect(internalSearchIds($criteria))->toBe([$fire->id]);
});

it('filters by created date with an inclusive end date', function () {
    Event::factory()->create(['created_at' => '2026-01-31 23:59:59']);
    $first = Event::factory()->create(['created_at' => '2026-02-01 00:00:00']);
    $last = Event::factory()->create(['created_at' => '2026-02-28 23:59:59']);
    Event::factory()->create(['created_at' => '2026-03-01 00:00:00']);

    $criteria = internalSearchCriteria(createdFrom: '2026-02-01', createdTo: '2026-02-28');

    expect(internalSearchIds($criteria))->toBe(collect([$first->id, $last->id])->sort()->values()->all());
});

it('selects the same events for the page and the export', function () {
    Storage::fake('local');

    $superAdmin = User::factory()->create(['super' => true]);

    $matching = Event::factory()->approved()->fires()->count(3)->sequence(
        ['name' => 'Matching fire one'],
        ['name' => 'Matching fire two'],
        ['name' => 'Matching fire three'],
    )->create([
        'is_private' => false,
        'created_at' => '2026-02-10 12:00:00',
    ]);
    Event::factory()->fires()->create(['approved_at' => null, 'created_at' => '2026-02-10 12:00:00']);
    Event::factory()->approved()->fires()->create(['is_private' => true, 'created_at' => '2026-02-10 12:00:00']);
    Event::factory()->approved()->survivalSchool()->create(['created_at' => '2026-02-10 12:00:00']);
    Event::factory()->approved()->fires()->create(['created_at' => '2026-03-10 12:00:00']);

    $params = [
        'event_type_ids' => [$matching->first()->event_type_id],
        'privacy' => 'public',
        'active_only' => '1',
        'created_from' => '2026-02-01',
        'created_to' => '2026-02-28',
        'fields' => ['name'],
    ];

    $pageEvents = collect();

    actingAs($superAdmin)
        ->get(route('internal.event-search', $params))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use (&$pageEvents) {
            $pageEvents = collect($page->toArray()['props']['results']['data']);
        });

    $csv = null;

    EventFacade::listen(MessageSent::class, function (MessageSent $sent) use (&$csv) {
        $csv = $sent->message->getAttachments()[0]->getBody();
    });

    actingAs($superAdmin)
        ->get(route('internal.event-search.export', $params))
        ->assertRedirect();

    $exportNames = collect(array_filter(explode("\n", $csv)))
        ->skip(1)
        ->map(fn (string $line) => str_getcsv($line, ',', '"', '')[0])
        ->sort()
        ->values()
        ->all();

    expect($pageEvents->pluck('id')->sort()->values()->all())->toBe($matching->pluck('id')->sort()->values()->all())
        ->and($exportNames)->toBe($pageEvents->pluck('name')->sort()->values()->all());
});

it('queues the export with the request criteria and fields', function () {
    Bus::fake();

    $superAdmin = User::factory()->create(['super' => true]);

    actingAs($superAdmin)
        ->get(route('internal.event-search.export', [
            'lat' => '51.5',
            'lng' => '-0.12',
            'event_type_ids' => [],
            'include_deleted' => 'true',
            'created_to' => '2026-02-28',
            'privacy' => 'private',
            'fields' => ['name', 'status'],
        ]))
        ->assertRedirect();

    Bus::assertDispatched(ExportEventSearchJob::class, function (ExportEventSearchJob $job) use ($superAdmin) {
        return $job->user->is($superAdmin)
            && $job->fields === ['name', 'status']
            && $job->criteria == internalSearchCriteria(
                lat: 51.5,
                lng: -0.12,
                radius: 30.0,
                activeOnly: true,
                includeDeleted: true,
                createdTo: '2026-02-28',
                privacy: EventVisibilityFilter::Private,
            );
    });
});

it('keeps the criteria intact when the export job is serialized for the queue', function () {
    $criteria = internalSearchCriteria(
        lat: 51.5,
        lng: -0.12,
        radius: 10.0,
        eventTypeIds: [1, 2],
        activeOnly: true,
        createdFrom: '2026-01-01',
        privacy: EventVisibilityFilter::Public,
    );

    $job = unserialize(serialize(new ExportEventSearchJob(User::factory()->create(), $criteria, ['name'])));

    expect($job->criteria)->toEqual($criteria)
        ->and($job->criteria->privacy)->toBe(EventVisibilityFilter::Public)
        ->and($job->fields)->toBe(['name']);
});

it('builds page cache keys from the criteria and page', function () {
    $criteria = internalSearchCriteria(activeOnly: true);

    expect($criteria->pageCacheKey(1))->toBe(internalSearchCriteria(activeOnly: true)->pageCacheKey(1))
        ->and($criteria->pageCacheKey(1))->not->toBe($criteria->pageCacheKey(2))
        ->and($criteria->pageCacheKey(1))->not->toBe(internalSearchCriteria(activeOnly: false)->pageCacheKey(1));
});

it('serves each page from its own cache entry', function () {
    $superAdmin = User::factory()->create(['super' => true]);

    Event::factory()->approved()->count(30)->create();

    $pageOneIds = [];
    $pageTwoIds = [];

    actingAs($superAdmin)
        ->get(route('internal.event-search'))
        ->assertInertia(function (AssertableInertia $page) use (&$pageOneIds) {
            $pageOneIds = collect($page->toArray()['props']['results']['data'])->pluck('id')->all();
        });

    actingAs($superAdmin)
        ->get(route('internal.event-search', ['page' => 2]))
        ->assertInertia(function (AssertableInertia $page) use (&$pageTwoIds) {
            $props = $page->toArray()['props'];
            $pageTwoIds = collect($props['results']['data'])->pluck('id')->all();

            expect($props['results']['meta']['current_page'])->toBe(2)
                ->and($props['results']['meta']['total'])->toBe(30);
        });

    expect($pageOneIds)->toHaveCount(25)
        ->and($pageTwoIds)->toHaveCount(5)
        ->and(array_intersect($pageOneIds, $pageTwoIds))->toBeEmpty();
});
