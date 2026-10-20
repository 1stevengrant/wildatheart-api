<?php

namespace App\Ai\Tools;

use Stringable;
use Laravel\Ai\Tools\Request;
use Laravel\Ai\Contracts\Tool;
use App\Queries\PlatformStatsQuery;
use App\Ai\Tools\Concerns\EncodesToolResults;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class PlatformStats implements Tool
{
    use EncodesToolResults;

    public function __construct(private PlatformStatsQuery $platformStats) {}

    public function description(): Stringable|string
    {
        return 'Headline platform numbers for fires, retreats and experiences (current listings, past year, countries, all time). The same figures the public website shows.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        return $this->encode($this->platformStats->get());
    }
}
