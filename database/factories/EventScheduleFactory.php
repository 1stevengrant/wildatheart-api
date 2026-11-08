<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSchedule;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventSchedule>
 */
class EventScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->fires(),
            'frequency' => FrequencyEnum::WEEKLY,
            'timezone' => 'America/Denver',
            'default_start_time' => '19:00',
            'default_end_time' => '21:00',
            'anchor_date' => now('America/Denver')->addWeek()->toDateString(),
        ];
    }

    public function weekly(): self
    {
        return $this->state(['frequency' => FrequencyEnum::WEEKLY]);
    }

    public function monthly(): self
    {
        return $this->state(['frequency' => FrequencyEnum::MONTHLY]);
    }

    public function custom(): self
    {
        return $this->state([
            'frequency' => FrequencyEnum::VARIES,
            'anchor_date' => null,
        ]);
    }
}
