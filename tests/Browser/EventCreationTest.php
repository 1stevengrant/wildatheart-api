<?php

use Illuminate\Support\Facades\DB;

it('can load the fire creation page when authenticated', function () {
    // Use the local database connection since browser hits wahfires.test
    $localConnection = DB::connection('local');

    // Find a user who has agreed to host agreement
    $userData = $localConnection->table('users')
        ->whereNotNull('given_name')
        ->whereNotNull('family_name')
        ->whereNotNull('agreed_to_host_agreement_date')
        ->first();

    if (! $userData) {
        $this->markTestSkipped('No eligible user found in local database');
    }

    // Login via test route and go directly to fire creation page
    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$userData->id}&redirect=/host/fire");

    // Verify the fire creation form is visible
    $page->assertSee('Tell us about your Fire');
    $page->assertPresent('input#name');
});
