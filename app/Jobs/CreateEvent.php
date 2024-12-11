<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $record, public int $userId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Event::create([
            'name' => $this->record['name'],
            'headline' => fake()->word(),
            'sex' => fake()->randomElement(['men', 'women']),
            'event_type_id' => EventType::inRandomOrder()->first()->id,
            'latitude' => $this->record['latitude_deg'],
            'longitude' => $this->record['longitude_deg'],
            'country' => $this->record['iso_country'],
            'state' => $this->record['iso_region'],
            'city' => $this->record['municipality'],
            'approved_at' => now(),
            'user_id' => $this->userId,
            'start_date' => fake()->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
        ]);
    }
}
