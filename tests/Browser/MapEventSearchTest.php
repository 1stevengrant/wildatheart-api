<?php

$recordEventSearches = <<<'JS'
    () => {
        window.eventSearches = [];

        new PerformanceObserver((entries) => {
            window.eventSearches.push(
                ...entries.getEntries()
                    .map((entry) => new URL(entry.name).searchParams)
                    .filter((params) => params.has('sw_latitude'))
                    .map((params) => Object.fromEntries(params))
            );
        }).observe({ type: 'resource', buffered: true });

        return true;
    }
    JS;

it('zooms out a limited number of times when there are no events in view', function () use ($recordEventSearches) {
    $page = $this->visit('https://wahfires.test');

    // Start over the empty South Pacific, zoomed in, with the location prompt dismissed.
    $page->script(<<<'JS'
        () => {
            localStorage.setItem('user_location', JSON.stringify({ latitude: -50, longitude: -130, timestamp: Date.now() }));
            localStorage.setItem('userZoom', '9');
            localStorage.setItem('location_modal_dismissed', Date.now().toString());

            return true;
        }
        JS);

    $page->refresh();
    $page->script($recordEventSearches);
    $page->wait(15);

    $searches = $page->script('() => window.eventSearches');
    $spans = array_map(fn (array $search) => $search['ne_latitude'] - $search['sw_latitude'], $searches);

    expect(count($searches))->toBeGreaterThan(1)->toBeLessThanOrEqual(6)
        ->and(end($spans))->toBeGreaterThan($spans[0] * 10);
});

it('says the search failed instead of showing no results', function () use ($recordEventSearches) {
    $page = $this->visit('https://wahfires.test');

    $page->script(<<<'JS'
        () => {
            localStorage.setItem('location_modal_dismissed', Date.now().toString());

            return true;
        }
        JS);

    $page->refresh();
    $page->script($recordEventSearches);
    $page->assertDontSee("We couldn't load events");

    $page->script(<<<'JS'
        () => {
            const open = XMLHttpRequest.prototype.open;

            XMLHttpRequest.prototype.open = function (method, url, ...rest) {
                const failingUrl = String(url).replace('/api/events/search', '/api/events/search-that-fails');

                return open.call(this, method, failingUrl, ...rest);
            };

            return true;
        }
        JS);

    $page->click('button:has-text("FOR men")');

    $page->assertSee("We couldn't load events")
        ->assertDontSee('No events matching your search');
});
