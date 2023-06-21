<?php

namespace App\Actions\Campfire;

use App\Models\Campfire;
use Lorisleiva\Actions\Concerns\AsAction;

class ApproveCampfire
{
    use AsAction;

    public function handle(
        Campfire $campfire
    ): void {
        $campfire->update([
            'approved_at' => now(),
        ]);
    }
}
