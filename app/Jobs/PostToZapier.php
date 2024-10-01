<?php

namespace App\Jobs;

use App\DTO\GoogleSheetDTO;
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
    public function __construct(public GoogleSheetDTO $dto) {}

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        activity()
            ->withProperties((array) $this->dto)
            ->log('Post to Zapier '.$this->dto->webhookUrl);

        if (config('services.zapier.enabled')) {

            $response = Http::post($this->dto->webhookUrl, (array) $this->dto);

            if ($response->failed()) {
                activity()
                    ->withProperties([
                        'data' => (array) $this->dto,
                        'response' => $response->body(),
                    ])
                    ->log('Post to Zapier failed '.$this->dto->webhookUrl);

                throw new \Exception('Post to Zapier failed '.$this->dto->webhookUrl);
            }
        }
    }
}
