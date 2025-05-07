<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

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
        LogActivityJob::dispatch(
            logName: 'zapier',
            description: 'Post to Zapier ' . $this->dataTransferObject->webhookUrl,
            properties: [
                'data' => (array) $this->dataTransferObject,
            ]
        );

        if (config('services.zapier.enabled')) {

            $response = Http::post($this->dataTransferObject->webhookUrl, (array) $this->dataTransferObject);

            if ($response->failed()) {
                LogActivityJob::dispatch(
                    logName: 'zapier',
                    description: 'Post to Zapier failed ' . $this->dataTransferObject->webhookUrl,
                    properties: [
                        'data' => (array) $this->dataTransferObject,
                        'response' => $response->body(),
                    ]
                );

                throw new \Exception('Post to Zapier failed ' . $this->dataTransferObject->webhookUrl);
            }
        }
    }
}
