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
            Http::post('https://hooks.zapier.com/hooks/catch/7935383/3tew72r/', [
                'email' => $data['email'],
                'quantity' => 1,
                'given_name' => $data['given_name'],
                'family_name' => $data['family_name'],
                'street_address' => $data['street_address'],
                'address2' => $data['address2'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip'],
                'country' => $data['country'],
                'date' => now(),
            ]);
        }

    }
}
