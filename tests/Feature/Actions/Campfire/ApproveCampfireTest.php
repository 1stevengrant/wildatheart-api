<?php

use App\Actions\Campfire\ApproveCampfire;
use App\Models\Campfire;

it('it approves a Fire', function () {
    $campfire = Campfire::factory()->create([
        'approved_at' => null,
    ]);

    $this->assertNull($campfire->approved_at);

    ApproveCampfire::run(campfire: $campfire);

    $campfire->refresh();

    $this->assertNotNull($campfire->approved_at);
});
