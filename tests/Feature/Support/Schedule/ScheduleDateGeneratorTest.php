<?php

use Carbon\CarbonImmutable;
use App\Enums\Event\FrequencyEnum;
use App\Support\Schedule\ScheduleDateGenerator;

function generatedDates(FrequencyEnum $frequency, string $anchor, string $after, int $count = 4): array
{
    return ScheduleDateGenerator::datesAfter(
        $frequency,
        CarbonImmutable::parse($anchor),
        CarbonImmutable::parse($after),
        $count,
    )->map(fn (CarbonImmutable $date) => $date->toDateString())->all();
}

it('repeats weekly from the anchor', function () {
    expect(generatedDates(FrequencyEnum::WEEKLY, '2026-12-08', '2026-12-07'))
        ->toBe(['2026-12-08', '2026-12-15', '2026-12-22', '2026-12-29']);
});

it('continues weekly after the last date across the year end', function () {
    expect(generatedDates(FrequencyEnum::WEEKLY, '2026-12-08', '2026-12-29'))
        ->toBe(['2027-01-05', '2027-01-12', '2027-01-19', '2027-01-26']);
});

it('keeps the weekday position for monthly', function () {
    // 2026-12-08 is the 2nd Tuesday of December.
    expect(generatedDates(FrequencyEnum::MONTHLY, '2026-12-08', '2026-12-07'))
        ->toBe(['2026-12-08', '2027-01-12', '2027-02-09', '2027-03-09']);
});

it('treats a 5th weekday anchor as the last weekday of each month', function () {
    // 2026-12-29 is the 5th Tuesday of December.
    expect(generatedDates(FrequencyEnum::MONTHLY, '2026-12-29', '2026-12-28'))
        ->toBe(['2026-12-29', '2027-01-26', '2027-02-23', '2027-03-30']);
});

it('keeps a 4th weekday as the 4th even when a month has five', function () {
    // 2026-12-22 is the 4th Tuesday; March 2027 has five Tuesdays.
    expect(generatedDates(FrequencyEnum::MONTHLY, '2026-12-22', '2027-02-28', 1))
        ->toBe(['2027-03-23']);
});

it('skips past occurrences when the last date is long gone', function () {
    expect(generatedDates(FrequencyEnum::WEEKLY, '2026-01-06', '2026-11-09', 2))
        ->toBe(['2026-11-10', '2026-11-17']);
});

it('labels the pattern', function (FrequencyEnum $frequency, string $anchor, string $label) {
    expect(ScheduleDateGenerator::patternLabel($frequency, CarbonImmutable::parse($anchor)))->toBe($label);
})->with([
    [FrequencyEnum::WEEKLY, '2026-12-08', 'Every Tuesday'],
    [FrequencyEnum::MONTHLY, '2026-12-08', '2nd Tuesday of the month'],
    [FrequencyEnum::MONTHLY, '2026-12-01', '1st Tuesday of the month'],
    [FrequencyEnum::MONTHLY, '2026-12-29', 'Last Tuesday of the month'],
]);

it('does not generate dates for custom schedules', function () {
    generatedDates(FrequencyEnum::VARIES, '2026-12-08', '2026-12-07');
})->throws(InvalidArgumentException::class);
