<?php

namespace App\Jobs\WeGive;

use App\Models\User;
use App\Jobs\LogActivityJob;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\UnsubscribeFromCommunicationListRequest;

class UnsubscribeFromCommunicationListJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly string $listId, public readonly array $donorIds, public readonly User $user) {}

    /**
     * Execute the job.
     */
    public function handle(WeGiveConnector $weGiveConnector): void
    {
        $unsubscribeFromListRequest = new UnsubscribeFromCommunicationListRequest(
            communicationListId: $this->listId,
            donorIds: $this->donorIds,
        );

        try {
            $unsubscribeFromListResponse = $weGiveConnector->send($unsubscribeFromListRequest);

            if ($unsubscribeFromListResponse->successful()) {
                LogActivityJob::dispatch(
                    logName: WeGiveConnector::INFO_LOG_NAME,
                    description: 'Unsubscribed user from WeGive communication list ' . $this->listId,
                    properties: [
                        'user_id' => $this->user->id,
                        'list_id' => $this->listId,
                    ]
                );
            }

        } catch (FatalRequestException|RequestException $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Failed to unsubscribe user from WeGive communication list',
                properties: [
                    'user_id' => $this->user->id,
                    'list_id' => $this->listId,
                    'error' => $e->getMessage(),
                ]
            );

        }
    }
}
