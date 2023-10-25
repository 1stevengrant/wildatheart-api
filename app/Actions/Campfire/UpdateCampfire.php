<?php

namespace App\Actions\Campfire;

use App\Models\Campfire;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCampfire
{
    use AsAction;

    public function handle(
        array $data,
        Campfire $campfire
    ): void {
        activity()->performedOn($campfire)->log('Update Campfire');
        $campfire->update($data);
    }
}
