<?php

namespace App\Data\InternalReporting;

use Spatie\LaravelData\Data;

class InternalReportingData extends Data
{
    /**
     * @param  array<string, ReportingMetricData>  $eventStats
     * @param  array<string, ReportingMetricData>  $yearStats
     */
    public function __construct(
        public string $date,
        public int $year,
        public array $eventStats,
        public array $yearStats,
    ) {}
}
