<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class PostToZapier implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if (app()->isProduction()) {
            $data = $this->data;
            Http::post(config('services.zapier.fire_webhook_url'), [
                'email' => $data['email'],
                'quantity' => 1,
                'first_name' => $data['given_name'],
                'last_name' => $data['family_name'],
                'address_1' => $data['street_address'],
                'address_2' => $data['address_2'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip'],
                'country' => $data['country'],
                'date' => now(),
            ]);
        }
    }
}
