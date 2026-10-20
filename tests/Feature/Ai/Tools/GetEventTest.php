<?php

use App\Models\User;
use App\Models\Event;
use App\Ai\Tools\GetEvent;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    Event::unsetEventDispatcher();
    User::unsetEventDispatcher();
});

it('gets an event with its host and managers', function () {
    $host = User::factory()->create(['given_name' => 'Ada', 'family_name' => 'Lovelace']);
    $manager = User::factory()->create(['given_name' => 'Alan', 'family_name' => 'Turing']);

    $event = Event::factory()->fires()->create(['user_id' => $host->id, 'name' => 'Lakeside Fire']);
    $event->managers()->attach($manager);

    $result = json_decode(app(GetEvent::class)->handle(new Request(['id' => $event->id])), true);

    expect($result['events'])->toHaveCount(1)
        ->and($result['events'][0])
        ->name->toBe('Lakeside Fire')
        ->host->name->toBe('Ada Lovelace')
        ->managers->toBe([['id' => $manager->id, 'name' => 'Alan Turing', 'email' => $manager->email]]);
});

it('searches events by name', function () {
    Event::factory()->fires()->create(['name' => 'Unique Ember Gathering']);

    $result = json_decode(app(GetEvent::class)->handle(new Request(['name' => 'Ember Gath'])), true);

    expect(collect($result['events'])->pluck('name')->all())->toBe(['Unique Ember Gathering']);
});

it('says when nothing matches', function () {
    $result = json_decode(app(GetEvent::class)->handle(new Request(['name' => 'no-such-event-name'])), true);

    expect($result)->toBe(['error' => 'No matching events found.']);
});
