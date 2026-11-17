<?php

namespace App\Data\InternalReporting;

use Spatie\LaravelData\Data;

class ReportingMetricData extends Data
{
    public function __construct(
        public int $count,
        public int $hosts,
    ) {}
}
