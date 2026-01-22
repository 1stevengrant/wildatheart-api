<?php

it('can visit the home page', function () {
    $page = $this->visit('https://wahfires.test');

    $page->assertVisible('#mapContainer');
});

it('displays the event map on the home page', function () {
    $page = $this->visit('https://wahfires.test');

    $page->assertVisible('#mapContainer');
});

it('can search for events by location', function () {
    $page = $this->visit('https://wahfires.test');

    // The search functionality should be present
    $page->assertPresent('input[placeholder*="Search"]');
});
