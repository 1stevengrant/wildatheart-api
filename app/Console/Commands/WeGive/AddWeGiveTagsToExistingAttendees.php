<?php

namespace App\Console\Commands\WeGive;

use Exception;
use App\Models\Attendee;
use App\Jobs\LogActivityJob;
use Illuminate\Console\Command;
use App\Jobs\WeGive\TagDonorJob;
use App\Http\Integrations\WeGive\WeGiveConnector;

class AddWeGiveTagsToExistingAttendees extends Command
{
    protected $signature = 'wegive:add-tags:existing-attendees';

    protected $description = 'Adds a WeGive tag to existing event attendees';

    public function handle(): int
    {

        Attendee::chunk(100, function ($attendees) {
            foreach ($attendees as $attendee) {
                try {
                    TagDonorJob::dispatch(
                        taggable: $attendee,
                        weGiveTagId: config('services.wegive.attendee_tag_id')
                    );
                } catch (Exception $e) {
                    dispatch(
                        new LogActivityJob(
                            logName: WeGiveConnector::ERROR_LOG_NAME,
                            description: 'AddWeGiveTagsToExistingHosts Error',
                            properties: [
                                'attendee_id' => $attendee->id,
                                'error' => $e->getMessage(),
                            ]
                        )
                    );
                    throw $e;
                }
            }
        });

        return 0;
    }
}
