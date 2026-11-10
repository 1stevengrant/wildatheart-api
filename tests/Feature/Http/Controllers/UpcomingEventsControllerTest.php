<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;

it('renders the upcoming events page', function (): void {
    $this->get(route('upcoming.events'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->component('UpcomingEventsPage'));
});

it('no longer serves the upcoming fires page', function (): void {
    expect(Route::has('upcoming.fires'))->toBeFalse();

    $this->get('/fires')->assertNotFound();
});
