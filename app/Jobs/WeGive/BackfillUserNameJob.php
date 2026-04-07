<?php

namespace App\Jobs\WeGive;

use App\Models\User;
use App\Jobs\LogActivityJob;
use App\Actions\User\UpdateUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\GetDonorRequest;

class BackfillUserNameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $maxExceptions = 3;

    public function __construct(public User $user) {}

    public function handle(UpdateUser $updateUser): void
    {
        if (! $this->user->wegive_donor_id) {
            return;
        }

        if ($this->user->given_name && $this->user->family_name) {
            return;
        }

        try {
            $connector = new WeGiveConnector;
            $response = $connector->send(new GetDonorRequest($this->user->wegive_donor_id));

            if (! $response->successful()) {
                LogActivityJob::dispatch(
                    logName: WeGiveConnector::WARNING_LOG_NAME,
                    description: 'Failed to fetch donor for name backfill',
                    properties: [
                        'user_id' => $this->user->id,
                        'wegive_donor_id' => $this->user->wegive_donor_id,
                        'status' => $response->status(),
                    ],
                    causedBy: $this->user,
                    performedOn: $this->user,
                );

                return;
            }

            $donor = $response->json('data') ?? $response->json();
            $givenName = $donor['first_name'] ?? null;
            $familyName = $donor['last_name'] ?? null;

            if (! $givenName && ! $familyName) {
                return;
            }

            $updateUser->handle($this->user, array_filter([
                'given_name' => $givenName ?? $this->user->given_name,
                'family_name' => $familyName ?? $this->user->family_name,
            ]));

            LogActivityJob::dispatch(
                logName: WeGiveConnector::INFO_LOG_NAME,
                description: 'Backfilled user name from WeGive',
                properties: [
                    'user_id' => $this->user->id,
                    'given_name' => $givenName,
                    'family_name' => $familyName,
                ],
                causedBy: $this->user,
                performedOn: $this->user,
            );
        } catch (FatalRequestException|RequestException $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Error fetching donor for name backfill',
                properties: [
                    'user_id' => $this->user->id,
                    'wegive_donor_id' => $this->user->wegive_donor_id,
                    'error' => $e->getMessage(),
                ],
                causedBy: $this->user,
                performedOn: $this->user,
            );

            throw $e;
        }
    }
}
