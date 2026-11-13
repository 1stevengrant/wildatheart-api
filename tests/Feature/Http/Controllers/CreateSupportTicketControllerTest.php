<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the support page gets the event types the home map needs', function () {
    $this->get(route('support.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('SupportRequestPage')
            ->has('allEventTypes')
            ->has('fireTypes')
            ->missing('experienceTypes')
            ->missing('retreatTypes'));
});
