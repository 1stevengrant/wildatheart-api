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

it('retreat: single-day in current year shows abbreviated month and day', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-15',
    ]);

    expect($event->badge_display)->toBe('Jun 15th');
});

it('retreat: single-day in future year shows abbreviated month, day, and year', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2027-06-15',
        'end_date' => '2027-06-15',
    ]);

    expect($event->badge_display)->toBe('Jun 15th 2027');
});

it('retreat: same-month range in current year shows condensed range', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-19',
    ]);

    expect($event->badge_display)->toBe('Jun 15th—19th');
});

it('retreat: same-month range in future year appends year to end', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2027-06-15',
        'end_date' => '2027-06-19',
    ]);

    expect($event->badge_display)->toBe('Jun 15th—19th 2027');
});

it('retreat: cross-month range in current year shows full month on both ends', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-06-28',
        'end_date' => '2026-07-05',
    ]);

    expect($event->badge_display)->toBe('Jun 28th—Jul 5th');
});

it('retreat: cross-month range in future year appends year to end', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2027-06-28',
        'end_date' => '2027-07-05',
    ]);

    expect($event->badge_display)->toBe('Jun 28th—Jul 5th 2027');
});

it('retreat: cross-year range hits cross-month branch and includes end year', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-12-28',
        'end_date' => '2027-01-05',
    ]);

    expect($event->badge_display)->toBe('Dec 28th—Jan 5th 2027');
});

it('retreat: missing end_date falls through to default full-month format', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2026-06-15',
        'end_date' => null,
    ]);

    expect($event->badge_display)->toBe('June 15th');
});

it('retreat: missing end_date in future year falls through with year', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => '2027-06-15',
        'end_date' => null,
    ]);

    expect($event->badge_display)->toBe('June 15th 2027');
});

it('retreat: missing start_date returns empty string', function () {
    $event = Event::factory()->wahRetreat()->create([
        'start_date' => null,
        'end_date' => '2026-06-19',
    ]);

    expect($event->badge_display)->toBe('');
});

it('experience: start_date in current year shows "Starts" prefix', function () {
    $event = Event::factory()->wahExperiences()->create([
        'start_date' => '2026-06-15',
    ]);

    expect($event->badge_display)->toBe('Starts Jun 15th');
});

it('experience: start_date in future year shows "Starts" prefix with year', function () {
    $event = Event::factory()->wahExperiences()->create([
        'start_date' => '2027-06-15',
    ]);

    expect($event->badge_display)->toBe('Starts Jun 15th 2027');
});

it('experience: missing start_date returns empty string', function () {
    $event = Event::factory()->wahExperiences()->create([
        'start_date' => null,
    ]);

    expect($event->badge_display)->toBe('');
});

it('fire: returns ucwords-formatted frequency label for weekly', function () {
    $event = Event::factory()->fires()->create([
        'frequency' => FrequencyEnum::WEEKLY,
    ]);

    expect($event->badge_display)->toBe('Weekly');
});

it('fire: returns ucwords-formatted frequency label for biweekly', function () {
    $event = Event::factory()->fires()->create([
        'frequency' => FrequencyEnum::BIWEEKLY,
    ]);

    expect($event->badge_display)->toBe('Biweekly');
});

it('fire: returns ucwords-formatted frequency label for monthly', function () {
    $event = Event::factory()->fires()->create([
        'frequency' => FrequencyEnum::MONTHLY,
    ]);

    expect($event->badge_display)->toBe('Monthly');
});

it('fire: returns ucwords-formatted frequency label for quarterly', function () {
    $event = Event::factory()->fires()->create([
        'frequency' => FrequencyEnum::QUARTERLY,
    ]);

    expect($event->badge_display)->toBe('Quarterly');
});

it('fire: returns "Monthly" when frequency is null', function () {
    $event = Event::factory()->fires()->create([
        'frequency' => null,
    ]);

    expect($event->badge_display)->toBe('Monthly');
});

it('fire: ignores start_date and always returns frequency label', function () {
    $event = Event::factory()->fires()->create([
        'frequency' => FrequencyEnum::WEEKLY,
        'start_date' => '2026-06-15',
    ]);

    expect($event->badge_display)->toBe('Weekly');
});
