<?php

use Illuminate\Support\Facades\DB;

it('links the Host button to the singular fire route after selecting Fires on the empty active events page', function () {
    // Browser hits wahfires.test, which is served against the local database.
    $localConnection = DB::connection('local');

    $userData = $localConnection->table('users')
        ->whereNotNull('given_name')
        ->whereNotNull('family_name')
        ->whereNotNull('phone')
        ->whereNotNull('email')
        ->whereNotNull('agreed_to_host_agreement_date')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('events')
                ->whereColumn('events.user_id', 'users.id');
        })
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('event_user')
                ->whereColumn('event_user.user_id', 'users.id');
        })
        ->first();

    if (! $userData) {
        $this->markTestSkipped('No eligible user without events found in local database');
    }

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$userData->id}&redirect=/events/active");

    $page->assertSee('You have no active events currently');

    $page->click('img[alt="Fires"]');

    // Regression guard: the Host link must point to the dedicated /host/fire route,
    // not the generic /host/eventType/fires fallback that getRouteForEventType replaces.
    $page->assertPresent('a[href$="/host/fire"]');
});
