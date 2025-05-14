<?php

namespace App\Jobs\Auth0;

use Exception;
use App\Jobs\LogActivityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Integrations\Auth0\Auth0Connector;
use App\Http\Integrations\Auth0\Requests\UpdateUserRequest;

class UpdateUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param  string  $auth0UserId  The Auth0 user ID (usually auth0|uuid format)
     * @param  string  $email  The new email address
     * @param  bool  $emailVerified  Whether the email is verified
     * @param  bool  $verifyEmail  Whether to trigger verification email
     */
    public function __construct(
        public string $auth0UserId,
        public string $email,
        public bool $emailVerified = true,
        public bool $verifyEmail = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(Auth0Connector $auth0Connector): void
    {
        try {

            $updateData = [
                'email' => $this->email,
                'email_verified' => $this->emailVerified,
            ];

            if ($this->verifyEmail) {
                $updateData['verify_email'] = true;
            }

            $updateRequest = new UpdateUserRequest(
                $this->auth0UserId,
                $updateData
            );

            $response = $auth0Connector->send($updateRequest);

            if (! $response->successful()) {

                dispatch(new LogActivityJob(
                    logName: 'auth0',
                    description: 'failed to update user email in Auth0',
                    properties: [
                        'auth0_user_id' => $this->auth0UserId,
                        'email' => $this->email,
                        'response' => $response->json(),
                        'status' => $response->status(),
                    ],
                ));

            } else {
                dispatch(new LogActivityJob(
                    logName: 'auth0',
                    description: 'updated user email in Auth0',
                    properties: [
                        'auth0_user_id' => $this->auth0UserId,
                        'email' => $this->email,
                        'response' => $response->json(),
                    ],
                ));
            }
        } catch (Exception $e) {

            dispatch(new LogActivityJob(
                logName: 'auth0',
                description: 'Exception when updating user email in Auth0',
                properties: [
                    'auth0_user_id' => $this->auth0UserId,
                    'email' => $this->email,
                    'exception' => $e->getMessage(),
                ],
            ));

            // Re-throw the exception to trigger job retry
            throw $e;
        }
    }
}
