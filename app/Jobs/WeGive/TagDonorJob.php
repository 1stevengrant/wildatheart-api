<?php

namespace App\Jobs\WeGive;

use Exception;
use App\Models\Event;
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
        protected Event $event,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function handle(): void
    {
        if (! $this->validateTaggingData()) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Missing required tagging data',
                properties: [
                    'event_id' => $this->event->id,
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));

            return;
        }

        $weGiveConnector = new WeGiveConnector;

        try {
            $this->tagDonor($weGiveConnector, $this->event->user->wegive_donor_id);
        } catch (Exception $e) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Unhandled exception in TagWeGiveDonorJob',
                properties: [
                    'event_id' => $this->event->id,
                    'exception' => $e->getMessage(),
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));

            throw $e;
        }
    }

    private function validateTaggingData(): bool
    {
        return $this->event->user
            && $this->event->user->wegive_donor_id
            && $this->event->we_give_tag;
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    private function tagDonor(WeGiveConnector $connector, string $donorId): void
    {
        $tagDonorsRequest = new TagDonorsRequest(
            donorIds: [$donorId],
            tagIds: [$this->event->we_give_tag],
        );

        try {
            $connector->send($tagDonorsRequest);

            dispatch(new LogActivityJob(
                logName: WeGiveConnector::INFO_LOG_NAME,
                description: 'Tagged donor',
                properties: [
                    'tag' => $this->event->we_give_tag,
                    'donor' => $donorId,
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));
        } catch (FatalRequestException|RequestException $e) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Error tagging donor',
                properties: [
                    'tag' => $this->event->we_give_tag,
                    'donor' => $donorId,
                    'error' => $e->getMessage(),
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));

            throw $e;
        }
    }
}
