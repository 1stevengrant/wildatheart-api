<?php

use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('the list Fire page renders without error', function (): void {
    $this->get(route('list.campfires'))
        ->assertStatus(200);
});

test('Host Fire component renders', function (): void {
    livewire(\App\Http\Livewire\ListCampfires::class)
        ->assertStatus(200);
});
