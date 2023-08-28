<?php

use App\Http\Livewire\ViewCampfire;
use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);
test('a general user should not see the update shipping address messaging', function () {
    $campfire = Campfire::factory()->create();

    livewire(ViewCampfire::class, ['campfire' => $campfire])
        ->assertDontSee('We want to fuel your Fire. Share your shipping address with us, and
            we’ll ship you a gift to get you going.');
});
