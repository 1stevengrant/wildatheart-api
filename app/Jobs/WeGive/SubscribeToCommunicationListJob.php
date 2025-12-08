<?php

namespace App\Jobs\WeGive;

use App\Models\User;
use App\Models\Attendee;
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
        public readonly Attendee|User $listMember
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WeGiveConnector $weGiveConnector): void
    {
        // Refresh the model to get the latest data from the database
        $this->listMember->refresh();

        // Skip if required fields are missing
        if (empty($this->listMember->given_name) || empty($this->listMember->family_name)) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Skipped subscribing user to WeGive communication list - missing name',
                properties: [
                    'user_id' => $this->listMember->id,
                    'list_id' => $this->listId,
                ]
            );

            return;
        }

        $subscribeToListRequest = new SubscribeToCommunicationListRequest(
            communicationListId: $this->listId,
            email: $this->listMember->email,
            firstName: $this->listMember->given_name,
            lastName: $this->listMember->family_name,
            phone: $this->listMember->phone,
        );

        try {
            $subscribeToListResponse = $weGiveConnector->send($subscribeToListRequest);

            if ($subscribeToListResponse->successful()) {
                LogActivityJob::dispatch(
                    logName: WeGiveConnector::INFO_LOG_NAME,
                    description: 'Subscribed user to WeGive communication list ' . $this->listId,
                    properties: [
                        'user_id' => $this->listMember->id,
                        'list_id' => $this->listId,
                    ]
                );
            }

        } catch (FatalRequestException|RequestException $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Failed to subscribe user to WeGive communication list',
                properties: [
                    'user_id' => $this->listMember->id,
                    'list_id' => $this->listId,
                    'error' => $e->getMessage(),
                ]
            );

        }
    }
}
