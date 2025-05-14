<?php

namespace App\Jobs\Salesforce;

use App\Models\User;
use App\Jobs\LogActivityJob;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleRecordInsert implements ShouldQueue
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

        $existingUser = User::query()->byEmail($newRecord['Email'])->first();

        if ($existingUser) {
            dispatch(new LogActivityJob(
                logName: 'salesforce',
                description: 'user already exists in Local database',
                properties: ['data' => $this->data],
                event: 'error',
            ));

            return;
        }

        $user = User::create([
            'given_name' => $newRecord['FirstName'],
            'family_name' => $newRecord['LastName'],
            'email' => $newRecord['Email'],
            'phone' => $newRecord['Phone'],
            'salesforce_contact_id' => $this->data['contactId'],
        ]);

        if ($user) {
            dispatch(new LogActivityJob(
                logName: 'salesforce',
                description: 'insert Local user from Salesforce insert',
                properties: ['data' => $this->data],
                event: 'insert',
            ));

            return;
        }

        dispatch(new LogActivityJob(
            logName: 'salesforce',
            description: 'user not created in Local database',
            properties: ['data' => $this->data],
            event: 'error',
        ));

    }
}
