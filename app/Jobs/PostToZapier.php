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
        dispatch(new LogActivityJob(
            logName: 'zapier',
            description: 'Post to Zapier '.$this->dataTransferObject->webhookUrl,
            properties: [
                'data' => (array) $this->dataTransferObject,
            ]
        ));

        if (config('services.zapier.enabled')) {

            $response = Http::post($this->dataTransferObject->webhookUrl, (array) $this->dataTransferObject);

            if ($response->failed()) {
                dispatch(new LogActivityJob(
                    logName: 'zapier',
                    description: 'Post to Zapier failed '.$this->dataTransferObject->webhookUrl,
                    properties: [
                        'data' => (array) $this->dataTransferObject,
                        'response' => $response->body(),
                    ]
                ));

                throw new \Exception('Post to Zapier failed '.$this->dataTransferObject->webhookUrl);
            }
        }
    }
}
