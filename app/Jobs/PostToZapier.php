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
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        if (app()->isProduction()) {
            activity()
                ->withProperties($this->data)
                ->log('Post to Zapier');
            $data = $this->data;
            $response = Http::post(config('services.zapier.event_webhook_url'), [
                'email' => $data['email'],
                'quantity' => 1,
                'first_name' => $data['given_name'],
                'last_name' => $data['family_name'],
                'address_1' => $data['street_address'],
                'address_2' => $data['address2'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip'],
                'country' => $data['country'],
                'date' => now(),
            ]);

            if ($response->failed()) {
                activity()
                    ->withProperties([
                        'data' => $this->data,
                        'response' => $response->body(),
                    ])
                    ->log('Post to Zapier failed');

                throw new \Exception('Post to Zapier failed');
            }
        }
    }
}
