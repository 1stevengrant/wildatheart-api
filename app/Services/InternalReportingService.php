<?php

namespace App\Services;

use Closure;
use App\Models\Event;
use Carbon\CarbonImmutable;
use App\Builders\EventBuilder;
use Illuminate\Support\Facades\Cache;
use App\Enums\Internal\ReportingEventType;
use App\Data\InternalReporting\ReportingMetricData;
use App\Data\InternalReporting\InternalReportingData;

class InternalReportingService
{
    private const CACHE_MINUTES = 15;

    public function forDate(CarbonImmutable $date): InternalReportingData
    {
        $eventStats = [];
        $yearStats = [];

        foreach (ReportingEventType::cases() as $eventType) {
            $eventStats[$eventType->value] = $this->createdSince($eventType, $date);
            $yearStats[$eventType->value] = $this->heldInYear($eventType, $date);
        }

        return new InternalReportingData(
            date: $date->toDateString(),
            year: $date->year,
            eventStats: $eventStats,
            yearStats: $yearStats,
        );
    }

    private function createdSince(ReportingEventType $eventType, CarbonImmutable $date): ReportingMetricData
    {
        $key = $eventType->value;
        $dateKey = $date->toDateString();

        $query = fn (): EventBuilder => $eventType->scope(
            Event::query()->createdAfter($dateKey)
        );

        return $this->metric(
            countKey: "{$key}.count_{$dateKey}",
            hostsKey: "{$key}.hosts_{$dateKey}",
            query: $query,
        );
    }

    private function heldInYear(ReportingEventType $eventType, CarbonImmutable $date): ReportingMetricData
    {
        $key = $eventType->value;
        $dateKey = $date->toDateString();
        $startOfYear = $date->startOfYear()->toDateTimeString();
        $endOfYear = $date->endOfYear()->toDateTimeString();

        $query = function () use ($eventType, $startOfYear, $endOfYear): EventBuilder {
            $query = $eventType->scope(Event::query()->withTrashed());

            if ($eventType->countsYearByUpdatedAt()) {
                return $query->whereBetween('updated_at', [$startOfYear, $endOfYear]);
            }

            return $query->startDateBetween($startOfYear, $endOfYear);
        };

        return $this->metric(
            countKey: "{$key}.count_year_{$dateKey}",
            hostsKey: "{$key}.hosts_year_{$dateKey}",
            query: $query,
        );
    }

    /**
     * @param  Closure(): EventBuilder  $query
     */
    private function metric(string $countKey, string $hostsKey, Closure $query): ReportingMetricData
    {
        $ttl = now()->addMinutes(self::CACHE_MINUTES);

        return new ReportingMetricData(
            count: Cache::remember($countKey, $ttl, fn (): int => $query()->count()),
            hosts: Cache::remember($hostsKey, $ttl, fn (): int => $query()->distinct('user_id')->count('user_id')),
        );
    }
}
