<?php

use App\Models\User;
use App\Models\Event;

beforeEach(function () {
    $this->withVite();
});

it('keeps unsaved event edits when a host is invited', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->create(['user_id' => $owner->id]);

    $this->actingAs($owner);

    $page = visit("/events/{$event->id}");

    $page->clear('#name')
        ->type('#name', 'Unsaved Fire Name')
        ->type('#host-email', 'new-host@example.com')
        ->press('Invite Host')
        ->assertSee('new-host@example.com')
        ->assertValue('#name', 'Unsaved Fire Name')
        ->assertValue('#host-email', '');
});
