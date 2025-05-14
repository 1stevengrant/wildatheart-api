<?php

namespace App\Jobs\WeGive;

use App\Models\User;
use App\Jobs\LogActivityJob;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\FindOrCreateDonorsRequest;

class CreateDonorJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $weGiveConnector = new WeGiveConnector;

        $request = new FindOrCreateDonorsRequest(
            email: $this->user->email,
            firstName: $this->user->given_name,
            lastName: $this->user->family_name,
        );

        try {
            $response = $weGiveConnector->send($request);

            if ($response->successful()) {
                $this->user->update(['wegive_donor_id' => $response->json()['data']['id']]);
            }

        } catch (FatalRequestException|RequestException $e) {

            dispatch(new LogActivityJob(
                logName: 'wegive',
                description: 'wegive donor creation failed',
                properties: ['exception' => $e->getMessage()],
            ));
        }

    }
}
