<?php

use App\Models\User;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

test('only a super admin can access the internal reporting url', function () {
    $user = User::factory()->create(['super' => false]);
    $response = $this->actingAs($user)->get(route('internal.reporting'));
    expect($response->status())->toBe(403);

    $user->super = true;
    $user->save();
    $response = $this->actingAs($user)->get(route('internal.reporting'));
    expect($response->status())->toBe(200);
});

test('it defaults the report date to 30 days ago', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-15 12:00:00'));

    $this->actingAs(User::factory()->create(['super' => true]))
        ->get(route('internal.reporting'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Internal/ReportingPage')
            ->where('date', '2026-05-16')
            ->where('year', 2026)
        );
});

test('it renders the reporting page contract for a given date', function () {
    Event::unsetEventDispatcher();

    Event::factory()->fires()->create(['created_at' => '2025-12-20 00:00:00', 'updated_at' => '2025-12-20 00:00:00']);

    $this->actingAs(User::factory()->create(['super' => true]))
        ->get(route('internal.reporting', ['date' => '2025-12-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Internal/ReportingPage')
            ->where('date', '2025-12-01')
            ->where('year', 2025)
            ->where('eventStats.fires', ['count' => 1, 'hosts' => 1])
            ->where('yearStats.fires', ['count' => 1, 'hosts' => 1])
            ->has('eventStats', 6)
            ->has('yearStats', 6)
            ->has('eventStats.experiences', fn (AssertableInertia $stat) => $stat
                ->where('count', 0)
                ->where('hosts', 0)
            )
        );
});

test('it rejects an invalid report date', function (string $date) {
    $this->actingAs(User::factory()->create(['super' => true]))
        ->get(route('internal.reporting', ['date' => $date]))
        ->assertSessionHasErrors('date');
})->with([
    'not a date' => ['banana'],
    'impossible date' => ['2026-02-30'],
    'wrong format' => ['15/06/2026'],
    'datetime' => ['2026-06-15T00:00:00'],
]);
