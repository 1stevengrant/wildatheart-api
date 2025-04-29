<?php

namespace App\Jobs\WeGive;

use App\Models\User;
use App\Jobs\LogActivityJob;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\SubscribeToCommunicationListRequest;

class SubscribeToCommunicationListJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $listId,
        public readonly User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WeGiveConnector $weGiveConnector): void
    {
        $subscribeToListRequest = new SubscribeToCommunicationListRequest(
            communicationListId: $this->listId,
            email: $this->user->email,
            firstName: $this->user->given_name,
            lastName: $this->user->family_name,
            phone: $this->user->phone,
        );

        try {
            $subscribeToListResponse = $weGiveConnector->send($subscribeToListRequest);

            if ($subscribeToListResponse->successful()) {
                LogActivityJob::dispatch(
                    logName: WeGiveConnector::INFO_LOG_NAME,
                    description: 'Subscribed user to WeGive communication list ' . $this->listId,
                    properties: [
                        'user_id' => $this->user->id,
                        'list_id' => $this->listId,
                    ]
                );
            }

        } catch (FatalRequestException|RequestException $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Failed to subscribe user to WeGive communication list',
                properties: [
                    'user_id' => $this->user->id,
                    'list_id' => $this->listId,
                    'error' => $e->getMessage(),
                ]
            );

        }
    }
}
