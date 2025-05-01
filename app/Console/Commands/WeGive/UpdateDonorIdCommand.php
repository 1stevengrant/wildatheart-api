<?php

namespace App\Console\Commands\WeGive;

use App\Models\User;
use App\Models\Attendee;
use App\Jobs\LogActivityJob;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use App\Http\Integrations\WeGive\WeGiveConnector;
use App\Http\Integrations\WeGive\Requests\FindOrCreateDonorsRequest;

class UpdateDonorIdCommand extends Command
{
    protected $signature = 'wegive:update-donor-ids {--model=all : The model to update (user, attendee, or all)}';

    protected $description = 'Update WeGive donor IDs for Users and/or Attendees against the WeGive API.';

    public function handle(WeGiveConnector $weGiveConnector): void
    {
        $modelOption = $this->option('model');

        if ($modelOption === 'user' || $modelOption === 'all') {
            $this->updateDonorIds(User::class, $weGiveConnector);
        }

        if ($modelOption === 'attendee' || $modelOption === 'all') {
            $this->updateDonorIds(Attendee::class, $weGiveConnector);
        }

        $this->info('All records processed.');
    }

    protected function updateDonorIds(string $modelClass, WeGiveConnector $weGiveConnector): void
    {
        $modelName = mb_strtolower(class_basename($modelClass));
        $records = $modelClass::whereNull('wegive_donor_id')->get();

        if ($records->isEmpty()) {
            $this->info("No {$modelName}s found without donor IDs.");

            return;
        }

        $this->info("Processing {$records->count()} {$modelName}s...");
        $this->output->progressStart($records->count());

        foreach ($records as $record) {
            $this->processRecord($record, $weGiveConnector, $modelName);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("All {$modelName}s processed.");
    }

    protected function processRecord(Model $record, WeGiveConnector $weGiveConnector, string $modelName): void
    {
        $firstName = $record->given_name ?? $record->first_name ?? '';
        $lastName = $record->family_name ?? $record->last_name ?? '';
        $email = $record->email;

        $findOrCreateDonorRequest = new FindOrCreateDonorsRequest(
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
        );

        try {
            $response = $weGiveConnector->send($findOrCreateDonorRequest);

            if ($response->successful()) {
                $donorId = $response->json('data.id');
                $record->update(['wegive_donor_id' => $donorId]);
                $this->line("Updated donor ID for $modelName $record->id");
            } else {
                LogActivityJob::dispatch(
                    logName: WeGiveConnector::ERROR_LOG_NAME,
                    description: "Failed to update donor ID for $modelName $record->id",
                    properties: [
                        "{$modelName}_id" => $record->id,
                        'error' => $response->body(),
                    ]
                );
            }
        } catch (\Exception $e) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: "Exception occurred while processing $modelName $record->id",
                properties: [
                    "{$modelName}_id" => $record->id,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
