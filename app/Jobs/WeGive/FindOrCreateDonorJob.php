<?php

namespace App\Jobs\WeGive;

use Exception;
use Throwable;
use JsonException;
use App\Models\User;
use App\Models\Attendee;
use App\Jobs\LogActivityJob;
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

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $maxExceptions = 3;

    public function __construct(
        public Attendee|User $donorModel,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws Throwable
     */
    public function handle(): void
    {
        $apiKey = config('services.wegive.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            return;
        }

        if (! $this->donorModel->email) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Missing required user data',
                properties: [
                    'donor_model_id' => $this->donorModel->id,
                ],
                causedBy: $this->donorModel,
                performedOn: $this->donorModel,
            );

            return;
        }

        try {
            if ($this->donorModel->wegive_donor_id) {
                LogActivityJob::dispatch(
                    logName: WeGiveConnector::INFO_LOG_NAME,
                    description: 'Using existing WeGive donor ID',
                    properties: [
                        'donor' => $this->donorModel->wegive_donor_id,
                    ],
                    causedBy: $this->donorModel,
                    performedOn: $this->donorModel,
                );

                return;
            }

            $weGiveConnector = new WeGiveConnector;
            $donorId = $this->findOrCreateDonor($weGiveConnector);

            if (! $donorId) {
                return;
            }

            DB::transaction(function () use ($donorId) {
                $this->donorModel->update([
                    'wegive_donor_id' => $donorId,
                ]);
            });

        } catch (Exception $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Unhandled exception in FindOrCreateDonorJob',
                properties: [
                    'donor_model_id' => $this->donorModel->id,
                    'exception' => $e->getMessage(),
                ],
                causedBy: $this->donorModel,
                performedOn: $this->donorModel,
            );

            throw $e;
        }
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
            'email' => $this->donorModel->email,
        ];

        if ($this->donorModel->given_name) {
            $requestData['firstName'] = $this->donorModel->given_name;
        }

        if ($this->donorModel->family_name) {
            $requestData['lastName'] = $this->donorModel->family_name;
        }

        if ($this->donorModel->phone) {
            $requestData['phone'] = $this->donorModel->phone;
        }

        $findOrCreateDonorRequest = new FindOrCreateDonorsRequest(...$requestData);

        try {
            $response = $connector->send($findOrCreateDonorRequest)->throw();
        } catch (FatalRequestException|RequestException $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Error creating donor',
                properties: [
                    'donor_model_id' => $this->donorModel->id,
                    'error' => $e->getMessage(),
                    'response' => $e instanceof RequestException ?
                        WeGiveConnector::loggableBody($e->getResponse()->body()) :
                        null,
                ],
                causedBy: $this->donorModel,
                performedOn: $this->donorModel,
            );

            if (WeGiveConnector::isPermanentFailure($e)) {
                $this->fail($e);
            }

            throw $e;
        }

        try {
            $donorId = $response->json('data.id');
        } catch (JsonException $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Invalid JSON response when creating donor',
                properties: [
                    'donor_model_id' => $this->donorModel->id,
                    'error' => $e->getMessage(),
                    'response' => WeGiveConnector::loggableBody($response->body()),
                ],
                causedBy: $this->donorModel,
                performedOn: $this->donorModel,
            );

            throw $e;
        }

        if (! $donorId) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Donor not found or created',
                properties: [
                    'donor_model_id' => $this->donorModel->id,
                    'response' => WeGiveConnector::loggableBody($response->body()),
                ],
                causedBy: $this->donorModel,
                performedOn: $this->donorModel,
            );

            return null;
        }

        LogActivityJob::dispatch(
            logName: WeGiveConnector::INFO_LOG_NAME,
            description: 'Created donor',
            properties: [
                'donor' => $donorId,
                'response' => $response->json(),
            ],
            causedBy: $this->donorModel,
            performedOn: $this->donorModel,
        );

        return $donorId;
    }
}
