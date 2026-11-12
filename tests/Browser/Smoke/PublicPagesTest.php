<?php

use App\Models\Event;

beforeEach(function () {
    $this->withVite();
});

it('renders the public page without JavaScript errors', function (string $path) {
    $page = visit($path);

    $page->script(<<<'JS'
        () => new Promise((resolve) => {
            const settled = () => document.querySelector('#app')?.childElementCount > 0
                && document.readyState === 'complete';

            const check = () => {
                if (settled() || window.__pestBrowser?.jsErrors?.length) {
                    setTimeout(resolve, 500);

                    return;
                }

                setTimeout(check, 50);
            };

            check();
        })
        JS);

    $page->assertNoJavaScriptErrors();
})->with([
    'home' => fn () => '/',
    'upcoming events' => fn () => '/upcoming',
    'support' => fn () => '/support',
    'event listing' => fn () => '/attend/' . Event::factory()->fires()->approved()->create()->id,
    'connect with host' => fn () => '/connect/' . Event::factory()->fires()->approved()->create()->id,
]);
