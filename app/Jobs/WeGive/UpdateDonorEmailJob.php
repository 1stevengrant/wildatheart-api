<?php

namespace App\Jobs\WeGive;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Integrations\WeGive\WeGiveConnector;
use App\Http\Integrations\WeGive\Requests\UpdateDonorRequest;

class UpdateDonorEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $donorId,
        public string $email
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WeGiveConnector $weGive): void
    {
        try {

            $updateRequest = new UpdateDonorRequest(
                $this->donorId,
                ['email_1' => $this->email]
            );

            $response = $weGive->send($updateRequest);

            if (! $response->successful()) {
                Log::error('Failed to update donor email in WeGive', [
                    'donor_id' => $this->donorId,
                    'email' => $this->email,
                    'response' => $response->json(),
                    'status' => $response->status(),
                ]);
            } else {
                Log::info('Successfully updated donor email in WeGive', [
                    'donor_id' => $this->donorId,
                    'email' => $this->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception when updating donor email in WeGive', [
                'donor_id' => $this->donorId,
                'email' => $this->email,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
