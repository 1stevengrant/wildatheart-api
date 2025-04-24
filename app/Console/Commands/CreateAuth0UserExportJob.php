<?php

namespace App\Console\Commands;

use App\Http\Integrations\Auth0\Auth0Connector;
use App\Http\Integrations\Auth0\Requests\CheckJobStatus;
use App\Http\Integrations\Auth0\Requests\StartUserExportJob;
use Illuminate\Console\Command;

class ExportAuth0Users extends Command
{
    protected $signature = 'app:export-auth0-users {connectionId}';

    protected $description = 'Exports all users from Auth0';

    public function handle()
    {

        $drupalProductionConnectionId = 'con_kguP5RYBejYNDfdI';
        $connectionId = $this->argument('connectionId');

        if (!$drupalProductionConnectionId) {
            $connectionId = $drupalProductionConnectionId;
        }

        $connector = new Auth0Connector;
        $startUserExportJobRequest = new StartUserExportJob(
            $connectionId,
            'json',
            [
                ["name" => 'email'],
                ["name" => 'name'],
                ['name' => 'family_name'],
                ['name' => 'given_name'],
                ['name' => 'user_metadata.uid'],
                ['name' => 'user_id'],
                ['name' => 'updated_at'],
                ['name' => 'email_verified'],
                ['name' => 'last_login'],
                ['name' => 'logins_count'],
                ['name' => 'blocked_for'],
                ['name' => 'last_ip'],
                ['name' => 'created_ip'],
                ['name' => 'picture'],
                ['name' => 'nickname'],
                ['name' => 'last_password_reset'],
                ['name' => 'identities'],
                ["name" => 'identities[0].connection', "export_as" => 'provider']
            ]
        );

        $response = $connector->send($startUserExportJobRequest);

        $jobId = $response->json('id');

        $checkJobStatusRequest = new CheckJobStatus(
            $jobId,
        );
        $response = $connector->send($checkJobStatusRequest);
        while ($response->json('status') !== 'completed') {
            $this->info("Job $jobId is not completed yet");
            sleep(10);
            $response = $connector->send($checkJobStatusRequest);
        }


        if ($response->json('location')) {
            $this->info("Job $jobId is completed");
            $this->info($response->json('location'));
        } else {
            $this->error("No location found in response");
            dd($response->json());
        }
    }
}
