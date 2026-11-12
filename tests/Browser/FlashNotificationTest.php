<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventOwnershipTransfer;

beforeEach(function () {
    $this->withVite();
});

it('styles an error flash as an error', function () {
    $event = Event::factory()->fires()->approved()->create();

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => 'someone-else@example.com',
        'transferred_by_user_id' => $event->user_id,
    ]);

    $this->actingAs(User::factory()->create());

    $page = visit("/accept-ownership-transfer/{$transfer->id}");

    $page->assertSee('This ownership transfer is for a different email address.');

    $classes = $page->script(<<<'JS'
        () => [...document.querySelectorAll('div')]
            .find((element) => element.className.includes('max-w-sm') && element.textContent.includes('different email address'))
            ?.className
        JS);

    expect($classes)->toContain('bg-r50');
});
