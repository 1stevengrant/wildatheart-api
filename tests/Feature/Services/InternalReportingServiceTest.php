<?php

use App\Models\User;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use App\Services\InternalReportingService;

beforeEach(function () {
    Event::unsetEventDispatcher();
    User::unsetEventDispatcher();

    $this->travelTo(CarbonImmutable::parse('2026-06-15 12:00:00'));

    $this->reportDate = CarbonImmutable::parse('2026-06-01');
});

function internalReport(CarbonImmutable $date): array
{
    return app(InternalReportingService::class)->forDate($date)->toArray();
}

it('reports the date and year it was asked for', function () {
    $report = internalReport($this->reportDate);

    expect($report['date'])->toBe('2026-06-01')
        ->and($report['year'])->toBe(2026)
        ->and(array_keys($report['eventStats']))->toBe([
            'fires',
            'wahRetreats',
            'survivalSchools',
            'captivatingRetreats',
            'captivatingFires',
            'experiences',
        ])
        ->and(array_keys($report['yearStats']))->toBe(array_keys($report['eventStats']));
});

it('counts each event type under its own key', function () {
    Event::factory()->fires()->create();
    Event::factory()->wahRetreat()->create();
    Event::factory()->survivalSchool()->create();
    Event::factory()->captivatingCoreRetreat()->create();
    Event::factory()->captivatingFires()->create();
    Event::factory()->wahExperiences()->create();
    Event::factory()->captivatingExperience()->create();
    Event::factory()->becomingAKingRetreat()->create();

    $eventStats = internalReport($this->reportDate)['eventStats'];

    expect($eventStats)->toBe([
        'fires' => ['count' => 1, 'hosts' => 1],
        'wahRetreats' => ['count' => 1, 'hosts' => 1],
        'survivalSchools' => ['count' => 1, 'hosts' => 1],
        'captivatingRetreats' => ['count' => 1, 'hosts' => 1],
        'captivatingFires' => ['count' => 1, 'hosts' => 1],
        'experiences' => ['count' => 2, 'hosts' => 2],
    ]);
});

it('only counts events created on or after the report date and excludes deleted ones', function () {
    Event::factory()->fires()->create(['created_at' => '2026-06-01 00:00:00']);
    Event::factory()->fires()->create(['created_at' => '2026-05-31 23:59:59']);
    Event::factory()->fires()->create(['created_at' => '2026-06-10 00:00:00'])->delete();

    expect(internalReport($this->reportDate)['eventStats']['fires'])
        ->toBe(['count' => 1, 'hosts' => 1]);
});

it('counts distinct hosts', function () {
    $host = User::factory()->create();

    Event::factory()->count(3)->fires()->create(['user_id' => $host->id]);
    Event::factory()->fires()->create();

    expect(internalReport($this->reportDate)['eventStats']['fires'])
        ->toBe(['count' => 4, 'hosts' => 2]);
});

it('counts non fire events by start date within the report year, including deleted ones', function () {
    Event::factory()->wahRetreat()->create(['start_date' => '2026-01-01 00:00:00']);
    Event::factory()->wahRetreat()->create(['start_date' => '2026-12-31 23:59:59']);
    Event::factory()->wahRetreat()->create(['start_date' => '2026-07-01 00:00:00'])->delete();
    Event::factory()->wahRetreat()->create(['start_date' => '2025-12-31 23:59:59']);
    Event::factory()->wahRetreat()->create(['start_date' => '2027-01-01 00:00:00']);

    expect(internalReport($this->reportDate)['yearStats']['wahRetreats'])
        ->toBe(['count' => 3, 'hosts' => 3]);
});

it('counts fires by when they were last updated within the report year', function (string $state, string $key) {
    Event::factory()->{$state}()->create([
        'start_date' => '2024-03-01 00:00:00',
        'updated_at' => '2026-02-01 00:00:00',
    ]);
    Event::factory()->{$state}()->create([
        'start_date' => '2026-03-01 00:00:00',
        'updated_at' => '2025-12-31 23:59:59',
    ]);
    Event::factory()->{$state}()->create([
        'updated_at' => '2026-12-31 23:59:59',
    ])->delete();

    expect(internalReport($this->reportDate)['yearStats'][$key])
        ->toBe(['count' => 2, 'hosts' => 2]);
})->with([
    'fires' => ['fires', 'fires'],
    'captivating fires' => ['captivatingFires', 'captivatingFires'],
]);

it('uses the year of the report date, not the current year', function () {
    Event::factory()->wahRetreat()->create(['start_date' => '2025-12-31 12:00:00']);
    Event::factory()->wahRetreat()->create(['start_date' => '2026-01-01 12:00:00']);

    $report = internalReport(CarbonImmutable::parse('2025-12-31'));

    expect($report['year'])->toBe(2025)
        ->and($report['yearStats']['wahRetreats'])->toBe(['count' => 1, 'hosts' => 1]);
});

it('caches each metric for 15 minutes under the existing keys', function () {
    Event::factory()->fires()->create();

    internalReport($this->reportDate);

    expect(Cache::get('fires.count_2026-06-01'))->toBe(1)
        ->and(Cache::get('fires.hosts_2026-06-01'))->toBe(1)
        ->and(Cache::get('fires.count_year_2026-06-01'))->toBe(1)
        ->and(Cache::get('fires.hosts_year_2026-06-01'))->toBe(1)
        ->and(Cache::has('experiences.count_2026-06-01'))->toBeTrue();

    Event::factory()->fires()->create();

    $this->travel(14)->minutes();

    expect(internalReport($this->reportDate)['eventStats']['fires']['count'])->toBe(1);

    $this->travel(2)->minutes();

    expect(internalReport($this->reportDate)['eventStats']['fires']['count'])->toBe(2);
});
