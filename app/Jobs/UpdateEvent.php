<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Event $event)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $meta = json_decode($this->event->legacy_drupal_location);

        $this->event->update([
            'latitude' => $meta->latitude,
            'longitude' => $meta->longitude,
            'country' => strtoupper($meta->country),
            'state' => $meta->province_name,
            'region' => $meta->province_name,
            'state_code' => strtoupper($meta->province),
            'city' => $meta->city,
            'street_address' => $meta->street,
            'zip' => $meta->postal_code,
        ]);
    }
}
