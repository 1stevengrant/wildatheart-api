<?php

use App\Models\User;
use App\Models\Event;

use function Pest\Laravel\actingAs;

use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['super' => true]);

    $this->publicEvent = Event::factory()->approved()->create(['is_private' => false]);
    $this->privateEvent = Event::factory()->approved()->create(['is_private' => true]);
});

it('returns only public events when privacy is public', function () {
    actingAs($this->superAdmin)
        ->get(route('internal.event-search', ['privacy' => 'public']))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $ids = collect($page->toArray()['props']['results']['data'])->pluck('id');

            expect($ids)->toContain($this->publicEvent->id)
                ->and($ids)->not->toContain($this->privateEvent->id);
        });
});

it('returns only private events when privacy is private', function () {
    actingAs($this->superAdmin)
        ->get(route('internal.event-search', ['privacy' => 'private']))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $ids = collect($page->toArray()['props']['results']['data'])->pluck('id');

            expect($ids)->toContain($this->privateEvent->id)
                ->and($ids)->not->toContain($this->publicEvent->id);
        });
});

it('returns both public and private events when privacy is all', function () {
    actingAs($this->superAdmin)
        ->get(route('internal.event-search', ['privacy' => 'all']))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $props = $page->toArray()['props'];
            $ids = collect($props['results']['data'])->pluck('id');

            expect($ids)->toContain($this->publicEvent->id)
                ->and($ids)->toContain($this->privateEvent->id)
                ->and($props['filters']['privacy'])->toBe('all');
        });
});

it('rejects an invalid privacy value', function () {
    actingAs($this->superAdmin)
        ->get(route('internal.event-search', ['privacy' => 'bogus']))
        ->assertSessionHasErrors('privacy');
});
