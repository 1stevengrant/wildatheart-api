<?php

it('puts the selected event types in the URL without reloading the page props', function () {
    $page = $this->visit('https://wahfires.test');

    $page->script(<<<'JS'
        () => {
            localStorage.setItem('location_modal_dismissed', Date.now().toString());

            return true;
        }
        JS);

    $page->refresh();

    $page->script(<<<'JS'
        () => {
            window.pageVisits = [];

            new PerformanceObserver((entries) => {
                window.pageVisits.push(
                    ...entries.getEntries()
                        .map((entry) => new URL(entry.name))
                        .filter((url) => url.origin === location.origin && url.pathname === '/')
                        .map((url) => url.href)
                );
            }).observe({ type: 'resource' });

            return true;
        }
        JS);

    $page->click('button:has-text("Showing events from map view")');
    $page->click('[role="switch"] >> nth=2');

    $types = $page->script(<<<'JS'
        new Promise((resolve) => {
            const deadline = Date.now() + 5000;

            const check = () => {
                const types = new URL(location.href).searchParams.get('types');

                if (types || Date.now() > deadline) {
                    resolve(types);

                    return;
                }

                setTimeout(check, 100);
            };

            check();
        })
        JS);

    $page->wait(1);

    expect($types)->toMatch('/^\d+$/')
        ->and($page->script('() => window.pageVisits'))->toBe([]);
});
