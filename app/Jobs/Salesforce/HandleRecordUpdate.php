<?php

namespace App\Jobs\Salesforce;

use App\Models\User;
use App\Jobs\LogActivityJob;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleRecordUpdate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $newRecord = $this->data['newRecord'];
        $oldRecord = $this->data['oldRecord'];
        $user = User::query()->byEmail($oldRecord['Email'])->first();

        if ($user) {
            $updateData = [
                'email' => $newRecord['Email'],
            ];

            if ($newRecord['FirstName'] !== $user->given_name) {
                $updateData['given_name'] = $newRecord['FirstName'];
            }

            if ($newRecord['LastName'] !== $user->family_name) {
                $updateData['family_name'] = $newRecord['LastName'];
            }

            if ($newRecord['Phone'] !== null) {
                $updateData['phone'] = $newRecord['Phone'];
            }

            if (! empty($updateData)) {
                $user->update($updateData);

                dispatch(new LogActivityJob(
                    logName: 'salesforce',
                    description: 'update Local user from Salesforce update',
                    properties: [
                        'oldRecord' => $oldRecord,
                        'newRecord' => $newRecord,
                    ],
                    event: 'update',
                ));
            }

            return;
        }

        dispatch(new LogActivityJob(
            logName: 'salesforce',
            description: 'user not found in local database',
            properties: [
                'oldRecord' => $oldRecord,
                'newRecord' => $newRecord,
            ],
        ));
    }
}
