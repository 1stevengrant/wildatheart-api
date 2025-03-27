<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class ReportData extends Data
{
    public function __construct(public array $headers, public array $data) {}
}
