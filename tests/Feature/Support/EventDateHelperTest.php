<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('adds the end date for a retreat event type', function () {
    $event = Event::factory()
        ->wahRetreat()
        ->create([
            'user_id' => $this->user->id,
        ]);

    $newStartDate = '2002-02-02';
    $this->put(route('update.event', $event), [
        'name' => 'New Event Name',
        'start_date' => $newStartDate,
        'city' => 'New City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => '123.456',
        'longitude' => '123.456',
        'region' => 'New Region',
        'district' => 'New District',
        'locality' => 'New Locality',
        'neighborhood' => 'New Neighborhood',
        'street_address' => 'New Street Address',
        'is_private' => 0,
        'state' => 'New State',
    ]);
    $endDate = Carbon::createFromFormat('Y-m-d', $newStartDate)->addDays(3);
    expect($event->refresh()->end_date->format('Y-m-d'))->toBe($endDate->format('Y-m-d'));

});

it('only updates the end date for a retreat and no other event type', function () {

    $newStartDate = '2002-02-02';
    $newData = [
        'name' => 'New Event Name',
        'start_date' => $newStartDate,
        'city' => 'New City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => '123.456',
        'longitude' => '123.456',
        'region' => 'New Region',
        'district' => 'New District',
        'locality' => 'New Locality',
        'neighborhood' => 'New Neighborhood',
        'street_address' => 'New Street Address',
        'is_private' => 0,
        'state' => 'New State',
    ];

    $wahExperience = Event::factory()
        ->wahExperiences()
        ->create([
            'user_id' => $this->user->id,
        ]);
    $this->put(route('update.event', $wahExperience), $newData);

    $fire = Event::factory()
        ->fires()
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->put(route('update.event', $fire), $newData);

    $becomingAKingExperience = Event::factory()
        ->becomingAKingExperience()
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->put(route('update.event', $becomingAKingExperience), $newData);

    $captivatingExperience = Event::factory()
        ->captivatingExperience()
        ->create([
            'user_id' => $this->user->id,
        ]);

    $this->put(route('update.event', $captivatingExperience), $newData);

    expect($wahExperience->refresh()->end_date)->toBeNull()
        ->and($captivatingExperience->refresh()->end_date)->toBeNull()
        ->and($becomingAKingExperience->refresh()->end_date)->toBeNull()
        ->and($fire->refresh()->end_date)->toBeNull();
});
