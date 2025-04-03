<?php

namespace App\Jobs\WeGive;

use Exception;
use Throwable;
use JsonException;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Actions\User\UpdateUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\FindOrCreateDonorsRequest;

class FindOrCreateDonorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $maxExceptions = 3;

    public function __construct(
        protected Event $event,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws Throwable
     */
    public function handle(): void
    {
        if (! $this->validateEventData()) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Missing required user data',
                properties: [
                    'event_id' => $this->event->id,
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));

            return;
        }

        try {
            if ($this->event->user->wegive_donor_id) {
                dispatch(new LogActivityJob(
                    logName: WeGiveConnector::INFO_LOG_NAME,
                    description: 'Using existing WeGive donor ID',
                    properties: [
                        'donor' => $this->event->user->wegive_donor_id,
                    ],
                    causedBy: $this->event->user,
                    performedOn: $this->event->user,
                ));

                dispatch(new TagDonorJob($this->event));

                return;
            }

            $weGiveConnector = new WeGiveConnector;
            $donorId = $this->findOrCreateDonor($weGiveConnector);

            if (! $donorId) {
                dispatch(new LogActivityJob(
                    logName: WeGiveConnector::ERROR_LOG_NAME,
                    description: 'Donor not found or created',
                    properties: [
                        'event_id' => $this->event->id,
                    ],
                    causedBy: $this->event->user,
                    performedOn: $this->event->user,
                ));

                return;
            }

            DB::transaction(function () use ($donorId) {
                UpdateUser::run(
                    $this->event->user,
                    [
                        'wegive_donor_id' => $donorId,
                    ]
                );
            });

            dispatch(new TagDonorJob($this->event));

        } catch (Exception $e) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Unhandled exception in FindOrCreateDonorJob',
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

    private function validateEventData(): bool
    {
        return $this->event->user && $this->event->user->email;
    }

    /**
     * Find or create a WeGive donor
     *
     * @throws FatalRequestException
     * @throws JsonException
     * @throws RequestException
     */
    private function findOrCreateDonor(WeGiveConnector $connector): ?string
    {
        $requestData = [
            'email' => $this->event->user->email,
        ];

        if ($this->event->user->given_name) {
            $requestData['firstName'] = $this->event->user->given_name;
        }

        if ($this->event->user->family_name) {
            $requestData['lastName'] = $this->event->user->family_name;
        }

        if ($this->event->user->phone) {
            $requestData['phone'] = $this->event->user->phone;
        }

        if ($this->event->user->primaryAddress()) {
            $requestData['address'] = $this->event->user->primaryAddress()->toArray();
        }

        $findOrCreateDonorRequest = new FindOrCreateDonorsRequest(...$requestData);

        try {
            $response = $connector->send($findOrCreateDonorRequest);

            dispatch(new LogActivityJob(
                logName: WeGiveConnector::INFO_LOG_NAME,
                description: 'Created donor',
                properties: [
                    'donor' => $response->json()['data']['id'] ?? null,
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));

            return $response->json()['data']['id'] ?? null;
        } catch (FatalRequestException|RequestException $e) {
            dispatch(new LogActivityJob(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Error creating donor',
                properties: [
                    'event_id' => $this->event->id,
                    'error' => $e->getMessage(),
                ],
                causedBy: $this->event->user,
                performedOn: $this->event->user,
            ));

            throw $e;
        }
    }
}
