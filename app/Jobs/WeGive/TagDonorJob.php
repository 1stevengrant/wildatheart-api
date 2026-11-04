<?php

namespace App\Jobs\WeGive;

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

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $maxExceptions = 3;

    public function __construct(
        public Attendee|User $taggable,
        public ?string $weGiveTagId,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function handle(): void
    {
        if (! $this->taggable->wegive_donor_id || ! $this->weGiveTagId) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Missing required tagging data',
                properties: [
                    'taggable_id' => $this->taggable->id,
                    'tag' => $this->weGiveTagId,
                ],
                causedBy: $this->taggable,
                performedOn: $this->taggable,
            );

            return;
        }

        $this->tagDonor(new WeGiveConnector, $this->taggable->wegive_donor_id);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    private function tagDonor(WeGiveConnector $connector, string $donorId): void
    {
        $tagDonorsRequest = new TagDonorsRequest(
            donorIds: [$donorId],
            tagIds: [$this->weGiveTagId],
        );

        try {
            $response = $connector->send($tagDonorsRequest)->throw();

            LogActivityJob::dispatch(
                logName: WeGiveConnector::INFO_LOG_NAME,
                description: 'Tagged donor',
                properties: [
                    'tag' => $this->weGiveTagId,
                    'donor' => $donorId,
                    'response' => WeGiveConnector::loggableBody($response->body()),
                ],
                causedBy: $this->taggable,
                performedOn: $this->taggable,
            );
        } catch (FatalRequestException|RequestException $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Error tagging donor',
                properties: [
                    'tag' => $this->weGiveTagId,
                    'donor' => $donorId,
                    'error' => $e->getMessage(),
                    'response' => $e instanceof RequestException ?
                        WeGiveConnector::loggableBody($e->getResponse()->body()) :
                        null,
                ],
                causedBy: $this->taggable,
                performedOn: $this->taggable,
            );

            if (WeGiveConnector::isPermanentFailure($e)) {
                $this->fail($e);
            }

            throw $e;
        }
    }
}
