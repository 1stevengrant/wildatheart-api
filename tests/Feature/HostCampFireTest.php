<?php

use App\Http\Livewire\HostCampfire;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole(User::ROLE_HOST);
    actingAs($this->user);
});

test('the Host 🔥 page renders without error', function (): void {
    $this->get(route('host'))
        ->assertStatus(200);
});

test('Host 🔥 component renders', function (): void {
    livewire(HostCampfire::class)
        ->assertStatus(200);
});

test('an authenticated user can post a new 🔥 they want to host', function () {

    $attributes = [
        'phone' => '1234567890',
        'address' => '123 Test St',
        'state' => 'California',
        'city' => 'Test City',
        'zip' => '55555',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
        'user_id' => $this->user->id,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle');

    $this->assertDatabaseHas('campfires', $attributes);
});

it('needs a phone number', function () {
    $attributes = [
        'phone' => '',
        'address' => '123 Test St',
        'state' => 'California',
        'city' => 'Test City',
        'zip' => '55555',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['phone' => 'required']);
});

it('needs an address', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '',
        'state' => 'California',
        'city' => 'Test City',
        'zip' => '55555',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['address' => 'required']);
});

it('needs a state', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 Main street',
        'state' => '',
        'city' => 'Test City',
        'zip' => '55555',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['state' => 'required']);
});

it('needs a city', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 main street',
        'state' => 'California',
        'city' => '',
        'zip' => '55555',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['city' => 'required']);
});

it('needs a zip', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 main street',
        'state' => 'California',
        'city' => 'Glasgow',
        'zip' => '',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['zip' => 'required']);
});

it('needs a date', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 main street',
        'state' => 'California',
        'city' => 'Glasgow',
        'zip' => '55555',
        'date' => '',
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['date' => 'required']);
});

it('needs a date > today', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 main street',
        'state' => 'California',
        'city' => 'Glasgow',
        'zip' => '55555',
        'date' => now()->subDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['date' => 'after:today']);
});

it('needs a time', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 main street',
        'state' => 'California',
        'city' => 'Glasgow',
        'zip' => '55555',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '',
        'is_private' => 0,
        'attendee_limit' => 50,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['time' => 'required']);
});

it('needs an attendee limit', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 main street',
        'state' => 'California',
        'city' => 'Glasgow',
        'zip' => '55555',
        'date' => '',
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => '',
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['attendeeLimit' => 'required']);
});

it('needs the attendee limit to be > 0', function () {
    $attributes = [
        'phone' => '0776649494',
        'address' => '123 main street',
        'state' => 'California',
        'city' => 'Glasgow',
        'zip' => '55555',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '12:00:00',
        'is_private' => 0,
        'attendee_limit' => 0,
    ];

    livewire(HostCampfire::class)
        ->set('phone', $attributes['phone'])
        ->set('address', $attributes['address'])
        ->set('state', $attributes['state'])
        ->set('city', $attributes['city'])
        ->set('zip', $attributes['zip'])
        ->set('date', $attributes['date'])
        ->set('time', $attributes['time'])
        ->set('isPrivate', $attributes['is_private'])
        ->set('attendeeLimit', $attributes['attendee_limit'])
        ->call('handle')
        ->assertHasErrors(['attendeeLimit' => 'min:1']);
});

test('an unauthenticated user is redirected to login trying to access host url', function () {
    auth()->logout();
    $this->get(route('host'))
        ->assertRedirect(route('social.login', ['provider' => 'auth0']));
});
