<?php

use App\Actions\Campfire\UpdateCampfire;
use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates a fire with given data', function () {
    $campfire = Campfire::factory()->create();

    $newData = ['name' => 'Steven'];

    UpdateCampfire::run(campfire: $campfire, data: $newData);

    $this->assertDatabaseHas('campfires', [
        'id' => $campfire->id,
        'name' => 'Steven',
    ]);
});
