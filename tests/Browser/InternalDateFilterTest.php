<?php

use App\Models\User;

function localSuperAdmin(): User
{
    // Browser hits wahfires.test, which is served against the local database.
    $admin = User::on('local')->where('super', true)->first();

    if (! $admin) {
        test()->markTestSkipped('No super admin found in local database');
    }

    return $admin;
}

it('shows the report date as the same calendar day west of UTC', function () {
    $admin = localSuperAdmin();

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$admin->id}&redirect=/internal/reporting?date=2026-11-20")
        ->withTimezone('America/Los_Angeles');

    $page->assertSee('November 20, 2026')
        ->assertDontSee('November 19, 2026');
});

it('searches by the created dates the admin picked east of UTC', function () {
    $admin = localSuperAdmin();

    $redirect = urlencode('/internal/event-search?created_from=2026-11-20&created_to=2026-11-21');

    $page = $this->visit("https://wahfires.test/_testing/login?user_id={$admin->id}&redirect={$redirect}")
        ->withTimezone('Pacific/Auckland');

    $page->assertSee('Event Search');

    $page->script(<<<'JS'
        () => {
            window.searchUrls = [];

            const open = XMLHttpRequest.prototype.open;

            XMLHttpRequest.prototype.open = function (method, url, ...rest) {
                window.searchUrls.push(String(url));

                return open.call(this, method, url, ...rest);
            };

            return true;
        }
        JS);

    $page->click('button.bg-blue-600:has-text("Search")');
    $page->wait(1);

    $query = $page->script(<<<'JS'
        () => {
            const url = window.searchUrls.find((url) => url.includes('created_from'));

            return url ? Object.fromEntries(new URL(url, location.origin).searchParams) : null;
        }
        JS);

    expect($query)->toMatchArray([
        'created_from' => '2026-11-20',
        'created_to' => '2026-11-21',
    ]);
});
