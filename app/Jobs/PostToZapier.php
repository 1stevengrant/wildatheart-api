<?php

namespace App\Jobs;

use App\DataTransferObjects\GoogleSheetDataTransferObject;
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
    public function __construct(public GoogleSheetDataTransferObject $dataTransferObject) {}

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        activity()
            ->withProperties((array) $this->dataTransferObject)
            ->log('Post to Zapier '.$this->dataTransferObject->webhookUrl);

        if (config('services.zapier.enabled')) {

            $response = Http::post($this->dataTransferObject->webhookUrl, (array) $this->dataTransferObject);

            if ($response->failed()) {
                activity()
                    ->withProperties([
                        'data' => (array) $this->dataTransferObject,
                        'response' => $response->body(),
                    ])
                    ->log('Post to Zapier failed '.$this->dataTransferObject->webhookUrl);

                throw new \Exception('Post to Zapier failed '.$this->dataTransferObject->webhookUrl);
            }
        }
    }
}
