<?php

namespace Database\Factories;

use App\Models\Event;
use Carbon\CarbonImmutable;
use App\Models\EventScheduleDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventScheduleDate>
 */
class EventScheduleDateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = CarbonImmutable::now('America/Denver')->addWeek()->startOfDay();

        return [
            'event_id' => Event::factory()->fires(),
            'date' => $date->toDateString(),
            'start_time' => null,
            'end_time' => null,
            'starts_at' => $date->setTime(19, 0)->utc(),
            'ends_at' => $date->setTime(21, 0)->utc(),
        ];
    }
}
