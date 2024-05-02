<?php

use App\Mail\Event\AttendeeConnectedToHost;
use App\Mail\Event\AttendeeRegistered;
use App\Models\EmailTemplate;
use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();
    $this->seed(\Database\Seeders\UserSeeder::class);
    $this->seed(\Database\Seeders\EmailTemplateSeeder::class);
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = Event::factory()->approved()->fires()->create();
    $this->attributes = [
        'family_name' => 'Doe',
        'given_name' => 'John',
        'email' => 'steven@ghijk.co.uk',
        'city' => 'London',
        'state' => 'London',
        'country' => 'United Kingdom',
        'phone' => '123456789',
        'opted_into_marketing' => true,
    ];

    $this->emailTemplate = EmailTemplate::factory()->create();
});

it('allows an anonymous user to see the form to connect with an event host', function () {

    $this->get(route('connect.index', $this->event))
        ->assertInertia(fn (Assert $assert) => $assert
            ->component('ConnectWithHost')
        );
});

it('makes a connection between the anonymous user and host', function () {
    $this->withoutExceptionHandling();
    $this->post(route('connect.store', $this->event), $this->attributes)
        ->assertRedirect(route('view.event', $this->event))
        ->assertSessionHas('notifications.message', "Connected with {$this->event->host->fullName}!");

    Mail::assertQueued(AttendeeRegistered::class);
    Mail::assertQueued(AttendeeConnectedToHost::class);
});

test('making a connection requires a first name', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['given_name' => '']))
        ->assertSessionHasErrors('given_name');
});

test('making a connection requires a last name', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['family_name' => '']))
        ->assertSessionHasErrors('family_name');
});

test('making a connection requires an email', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['email' => '']))
        ->assertSessionHasErrors('email');
});

test('making a connection requires a valid email', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['email' => 'setev@@@@icloud.com']))
        ->assertSessionHasErrors('email');
});

test('making a connection requires a city for the attendee', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['city' => '']))
        ->assertSessionHasErrors('city');
});

test('making a connection requires a state for the attendee', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['state' => '']))
        ->assertSessionHasErrors('state');
});

test('making a connection requires a phone for the attendee', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['phone' => '']))
        ->assertSessionHasErrors('phone');
});

test('phone number cannot exceed 20 characters', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['phone' => str_repeat('1',
        21)]))
        ->assertSessionHasErrors('phone');
});

test('phone number must have min 9 characters', function () {

    $this->post(route('connect.store', $this->event), array_merge($this->attributes, ['phone' => str_repeat('1',
        8)]))
        ->assertSessionHasErrors('phone');
});
