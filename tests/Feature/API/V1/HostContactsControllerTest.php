<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['auth0.base_url' => 'https://auth.example.test']);

    $this->host = User::factory()->create(['email' => 'host@example.test']);
    $this->event = Event::factory()->fires()->approved()->for($this->host)->create([
        'name' => 'Becoming a King Fire',
    ]);

    Http::fake([
        'auth.example.test/userinfo' => Http::response([
            'email' => $this->host->email,
            'email_verified' => true,
        ]),
    ]);
});

function hostBearer(): array
{
    return ['Authorization' => 'Bearer fake.userinfo.token'];
}

it('returns 401 when no bearer token is provided', function () {
    $this->getJson('/api/v1/host/contacts')->assertUnauthorized();
});

it('lists contacts for events the host owns', function () {
    $interestedUser = User::factory()->create([
        'given_name' => 'Alice',
        'family_name' => 'Adams',
        'email' => 'alice@example.test',
    ]);

    DB::table('event_user_interests')->insert([
        'user_id' => $interestedUser->id,
        'event_id' => $this->event->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.user_id'))->toBe($interestedUser->id)
        ->and($response->json('data.0.event_id'))->toBe($this->event->id)
        ->and($response->json('data.0.event_name'))->toBe('Becoming a King Fire')
        ->and($response->json('data.0.email'))->toBe('alice@example.test')
        ->and($response->json('data.0.given_name'))->toBe('Alice')
        ->and($response->json('data.0.archived_at'))->toBeNull();
});

it('includes contacts for events the host manages', function () {
    $otherHost = User::factory()->create();
    $managedEvent = Event::factory()->fires()->approved()->for($otherHost)->create([
        'name' => 'Managed Event',
    ]);
    $managedEvent->managers()->attach($this->host);

    $interestedUser = User::factory()->create();
    DB::table('event_user_interests')->insert([
        'user_id' => $interestedUser->id,
        'event_id' => $managedEvent->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.event_id'))->toBe($managedEvent->id);
});

it('excludes contacts from events the host does not own or manage', function () {
    $otherHost = User::factory()->create();
    $otherEvent = Event::factory()->fires()->approved()->for($otherHost)->create();
    $interestedUser = User::factory()->create();
    DB::table('event_user_interests')->insert([
        'user_id' => $interestedUser->id,
        'event_id' => $otherEvent->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts')
        ->assertOk();

    expect($response->json('data'))->toBeEmpty();
});

it('returns one row per (user, event) interest', function () {
    $secondEvent = Event::factory()->fires()->approved()->for($this->host)->create([
        'name' => 'Second Event',
    ]);

    $contact = User::factory()->create();

    DB::table('event_user_interests')->insert([
        ['user_id' => $contact->id, 'event_id' => $this->event->id, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $contact->id, 'event_id' => $secondEvent->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('sorts by name asc and desc', function () {
    $alice = User::factory()->create(['given_name' => 'Alice', 'email' => 'alice@example.test']);
    $bob = User::factory()->create(['given_name' => 'Bob', 'email' => 'bob@example.test']);

    DB::table('event_user_interests')->insert([
        ['user_id' => $alice->id, 'event_id' => $this->event->id, 'created_at' => now()->subMinute(), 'updated_at' => now()],
        ['user_id' => $bob->id, 'event_id' => $this->event->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $asc = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?sort=name')
        ->assertOk();

    expect($asc->json('data.0.given_name'))->toBe('Alice')
        ->and($asc->json('data.1.given_name'))->toBe('Bob');

    $desc = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?sort=-name')
        ->assertOk();

    expect($desc->json('data.0.given_name'))->toBe('Bob')
        ->and($desc->json('data.1.given_name'))->toBe('Alice');
});

it('sorts by email asc and desc', function () {
    $alice = User::factory()->create(['given_name' => 'Alice', 'email' => 'alice@example.test']);
    $zed = User::factory()->create(['given_name' => 'Zed', 'email' => 'zed@example.test']);

    DB::table('event_user_interests')->insert([
        ['user_id' => $alice->id, 'event_id' => $this->event->id, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $zed->id, 'event_id' => $this->event->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $asc = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?sort=email')
        ->assertOk();

    expect($asc->json('data.0.email'))->toBe('alice@example.test')
        ->and($asc->json('data.1.email'))->toBe('zed@example.test');

    $desc = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?sort=-email')
        ->assertOk();

    expect($desc->json('data.0.email'))->toBe('zed@example.test');
});

it('sorts by created_at asc and desc with desc as the default', function () {
    $older = User::factory()->create();
    $newer = User::factory()->create();

    DB::table('event_user_interests')->insert([
        ['user_id' => $older->id, 'event_id' => $this->event->id, 'created_at' => now()->subDay(), 'updated_at' => now()],
        ['user_id' => $newer->id, 'event_id' => $this->event->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $default = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts')
        ->assertOk();
    expect($default->json('data.0.user_id'))->toBe($newer->id);

    $asc = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?sort=created_at')
        ->assertOk();
    expect($asc->json('data.0.user_id'))->toBe($older->id);
});

it('filters by event name (partial match)', function () {
    $kingsEvent = $this->event;
    $retreatEvent = Event::factory()->fires()->approved()->for($this->host)->create([
        'name' => 'Wilderness Retreat',
    ]);

    $contact = User::factory()->create();
    DB::table('event_user_interests')->insert([
        ['user_id' => $contact->id, 'event_id' => $kingsEvent->id, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $contact->id, 'event_id' => $retreatEvent->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?filter[event]=Retreat')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.event_name'))->toBe('Wilderness Retreat');
});

it('hides archived contacts by default and reveals them with filter[archived]=true', function () {
    $active = User::factory()->create();
    $archived = User::factory()->create();

    DB::table('event_user_interests')->insert([
        ['user_id' => $active->id, 'event_id' => $this->event->id, 'created_at' => now(), 'updated_at' => now(), 'archived_at' => null],
        ['user_id' => $archived->id, 'event_id' => $this->event->id, 'created_at' => now(), 'updated_at' => now(), 'archived_at' => now()],
    ]);

    $defaultResponse = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts')
        ->assertOk();
    expect($defaultResponse->json('data'))->toHaveCount(1)
        ->and($defaultResponse->json('data.0.user_id'))->toBe($active->id);

    $archivedResponse = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?filter[archived]=true')
        ->assertOk();
    expect($archivedResponse->json('data'))->toHaveCount(1)
        ->and($archivedResponse->json('data.0.user_id'))->toBe($archived->id);

    $allResponse = $this->withHeaders(hostBearer())
        ->getJson('/api/v1/host/contacts?filter[archived]=all')
        ->assertOk();
    expect($allResponse->json('data'))->toHaveCount(2);
});

it('archives a contact via POST', function () {
    $contact = User::factory()->create();
    DB::table('event_user_interests')->insert([
        'user_id' => $contact->id,
        'event_id' => $this->event->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->withHeaders(hostBearer())
        ->postJson("/api/v1/host/contacts/{$this->event->id}/{$contact->id}/archive")
        ->assertOk();

    expect($response->json('archived_at'))->not->toBeNull();

    $row = DB::table('event_user_interests')
        ->where('user_id', $contact->id)
        ->where('event_id', $this->event->id)
        ->first();

    expect($row->archived_at)->not->toBeNull();
});

it('unarchives a contact via DELETE', function () {
    $contact = User::factory()->create();
    DB::table('event_user_interests')->insert([
        'user_id' => $contact->id,
        'event_id' => $this->event->id,
        'created_at' => now(),
        'updated_at' => now(),
        'archived_at' => now(),
    ]);

    $response = $this->withHeaders(hostBearer())
        ->deleteJson("/api/v1/host/contacts/{$this->event->id}/{$contact->id}/archive")
        ->assertOk();

    expect($response->json('archived_at'))->toBeNull();

    $row = DB::table('event_user_interests')
        ->where('user_id', $contact->id)
        ->where('event_id', $this->event->id)
        ->first();

    expect($row->archived_at)->toBeNull();
});

it('returns 404 when archiving a contact for an event the host does not own or manage', function () {
    $otherHost = User::factory()->create();
    $otherEvent = Event::factory()->fires()->approved()->for($otherHost)->create();

    $contact = User::factory()->create();
    DB::table('event_user_interests')->insert([
        'user_id' => $contact->id,
        'event_id' => $otherEvent->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->withHeaders(hostBearer())
        ->postJson("/api/v1/host/contacts/{$otherEvent->id}/{$contact->id}/archive")
        ->assertNotFound();
});

it('returns 404 when archiving a (user, event) pair with no interest', function () {
    $stranger = User::factory()->create();

    $this->withHeaders(hostBearer())
        ->postJson("/api/v1/host/contacts/{$this->event->id}/{$stranger->id}/archive")
        ->assertNotFound();
});
