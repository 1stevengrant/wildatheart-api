<?php

use Carbon\Carbon;
use App\Models\Event;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    Carbon::setTestNow('2026-05-14');
});

afterEach(function () {
    Carbon::setTestNow();
});

it('retreat: single-day in current year matches badge_display', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-15',
    ]);

    expect($event->formatted_dates)->toBe('Jun 15th')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});

it('retreat: same-month range in current year matches badge_display', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-19',
    ]);

    expect($event->formatted_dates)->toBe('Jun 15th—19th')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});

it('retreat: cross-month range in future year matches badge_display', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2027-06-28',
        'end_date' => '2027-07-05',
    ]);

    expect($event->formatted_dates)->toBe('Jun 28th—Jul 5th 2027')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});

it('retreat: missing end_date falls through to default full-month format', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-06-15',
        'end_date' => null,
    ]);

    expect($event->formatted_dates)->toBe('June 15th')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});

it('experience: current year shows "Starts" prefix', function () {
    $event = Event::factory()->wahExperiences()->create([
        'start_date' => '2026-06-15',
    ]);

    expect($event->formatted_dates)->toBe('Starts Jun 15th')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});

it('experience: future year shows "Starts" prefix with year', function () {
    $event = Event::factory()->wahExperiences()->create([
        'start_date' => '2027-06-15',
    ]);

    expect($event->formatted_dates)->toBe('Starts Jun 15th 2027')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});

it('experience: missing start_date returns empty string', function () {
    $event = Event::factory()->wahExperiences()->create([
        'start_date' => null,
    ]);

    expect($event->formatted_dates)->toBe('')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});

it('fire: returns frequency label even though V1 callers skip fires', function () {
    $event = Event::factory()->fires()->create([
        'frequency' => FrequencyEnum::WEEKLY,
    ]);

    expect($event->formatted_dates)->toBe('Weekly')
        ->and($event->formatted_dates)->toBe($event->badge_display);
});
