<?php

use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('the list 🔥 page renders without error', function (): void {
    $this->get(route('list.campfires'))
        ->assertStatus(200);
});

test('Host 🔥 component renders', function (): void {
    livewire(\App\Http\Livewire\ListCampfires::class)
        ->assertStatus(200);
});
