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

it('flies the map to the visitor when they use their location after the map has loaded', function () {
    $recordEventSearches = <<<'JS'
        () => {
            window.eventSearches = [];

            new PerformanceObserver((entries) => {
                window.eventSearches.push(
                    ...entries.getEntries()
                        .map((entry) => new URL(entry.name).searchParams)
                        .filter((params) => params.has('sw_latitude'))
                );
            }).observe({ type: 'resource', buffered: true });

            return true;
        }
        JS;

    $waitForSearchWhere = fn (string $condition): string => <<<JS
        new Promise((resolve) => {
            const deadline = Date.now() + 20000;

            const check = () => {
                const found = window.eventSearches.some((params) => {$condition});

                if (found || Date.now() > deadline) {
                    resolve(found);

                    return;
                }

                setTimeout(check, 100);
            };

            check();
        })
        JS;

    $boundsContainAuckland = <<<'JS'
        parseFloat(params.get('sw_latitude')) < -36.8485
            && parseFloat(params.get('ne_latitude')) > -36.8485
            && (174.7633 - parseFloat(params.get('sw_longitude')) + 360) % 360
                < (parseFloat(params.get('ne_longitude')) - parseFloat(params.get('sw_longitude')) + 360) % 360
        JS;

    $page = $this->visit('https://wahfires.test')->geolocation(-36.8485, 174.7633);

    $page->assertSee('Use Your Location?');

    $page->script($recordEventSearches);

    expect($page->script($waitForSearchWhere('true')))->toBeTrue();

    $page->click('Use My Location');

    expect($page->script($waitForSearchWhere($boundsContainAuckland)))->toBeTrue();

    $page->assertDontSee('Use Your Location?');
});
