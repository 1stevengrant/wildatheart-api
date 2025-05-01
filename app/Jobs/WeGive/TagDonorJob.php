<?php

namespace App\Jobs\WeGive;

use JsonException;
use App\Models\User;
use App\Models\Attendee;
use App\Jobs\LogActivityJob;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\TagDonorsRequest;

class TagDonorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public int $tries = 3;

    public int $backoff = 60;

    public int $maxExceptions = 3;

    public function __construct(
        protected Attendee|User $taggable,
        protected string $weGiveTagId,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function handle(): void
    {
        if (! $this->taggable->wegive_donor_id) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Missing required tagging data',
                properties: [
                    'taggable_id' => $this->taggable->id,
                ],
                causedBy: $this->taggable,
                performedOn: $this->taggable,
            ));

            return;
        }

        $weGiveConnector = new WeGiveConnector;

        try {
            $this->tagDonor($weGiveConnector, $this->taggable->wegive_donor_id);
        } catch (FatalRequestException|RequestException $e) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Unhandled exception in TagWeGiveDonorJob',
                properties: [
                    'taggable_id' => $this->taggable->id,
                    'error' => $e->getMessage(),
                    'response' => $e->getResponse() && method_exists($e->getResponse(), 'json') ?
                        ($e->getResponse()->json() ?? 'Invalid JSON') :
                        null,
                ],
                causedBy: $this->taggable,
                performedOn: $this->taggable,
            ));

            throw $e;
        }
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    private function tagDonor(WeGiveConnector $connector, string $donorId): void
    {
        $tagDonorsRequest = new TagDonorsRequest(
            donorIds: [$donorId],
            tagIds: [$this->weGiveTagId],
        );

        try {
            $response = $connector->send($tagDonorsRequest);

            dispatch(new LogActivityJob(
                logName: WeGiveConnector::INFO_LOG_NAME,
                description: 'Tagged donor',
                properties: [
                    'tag' => $this->weGiveTagId,
                    'donor' => $donorId,
                    'response' => $response->json() && method_exists($response, 'json') ?
                        ($response->json() ?? 'Invalid JSON') :
                        null,
                ],
                causedBy: $this->taggable,
                performedOn: $this->taggable,
            ));
        } catch (FatalRequestException|RequestException $e) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Error tagging donor',
                properties: [
                    'tag' => $this->weGiveTagId,
                    'donor' => $donorId,
                    'error' => $e->getMessage(),
                    'response' => $e->getResponse() && method_exists($e->getResponse(), 'json') ?
                        ($e->getResponse()->json() ?? 'Invalid JSON') :
                        null,
                ],
                causedBy: $this->taggable,
                performedOn: $this->taggable,
            ));

            throw $e;
        }
    }
}
