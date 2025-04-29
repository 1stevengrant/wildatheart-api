<?php

namespace App\Console\Commands\WeGive;

use App\Jobs\LogActivityJob;
use Illuminate\Console\Command;
use App\Http\Integrations\WeGive\WeGiveConnector;
use App\Http\Integrations\WeGive\Requests\FindOrCreateDonorsRequest;

class UpdateUserDonorIdCommand extends Command
{
    protected $signature = 'wegive:update-donor-ids';

    protected $description = 'Loop through all users and update their WeGive donor IDs against the WeGive API.';

    public function handle(WeGiveConnector $weGiveConnector)
    {
        $users = \App\Models\User::whereNull('wegive_donor_id')->get();

        $this->output->progressStart($users->count());

        foreach ($users as $user) {
            $findOrCreateDonorRequest = new FindOrCreateDonorsRequest(
                email: $user->email,
                firstName: $user->given_name,
                lastName: $user->family_name,
            );

            try {
                $response = $weGiveConnector->send($findOrCreateDonorRequest);

                if ($response->successful()) {
                    $donorId = $response->json('data.id');
                    $user->update(['wegive_donor_id' => $donorId]);
                    $this->info('Updated donor ID for user ' . $user->id);
                } else {
                    LogActivityJob::dispatch(
                        logName: WeGiveConnector::ERROR_LOG_NAME,
                        description: 'Failed to update donor ID for user ' . $user->id,
                        properties: [
                            'user_id' => $user->id,
                            'error' => $response->body(),
                        ]
                    );
                }
            } catch (\Exception $e) {

                LogActivityJob::dispatch(
                    logName: WeGiveConnector::ERROR_LOG_NAME,
                    description: 'Exception occurred while processing user ' . $user->id,
                    properties: [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]
                );
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info('All users processed.');
    }
}
