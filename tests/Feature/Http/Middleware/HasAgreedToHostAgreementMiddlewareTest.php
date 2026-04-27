<?php

use App\Models\User;
use App\Models\EventType;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->user = User::factory()->create([
        'agreed_to_host_agreement_date' => null,
    ]);

    $this->eventType = EventType::first();
});

it('redirects to host agreement when user has not agreed yet', function () {
    $this->actingAs($this->user)
        ->get(route('host.eventType.index', ['eventType' => $this->eventType]))
        ->assertRedirect(route('host.agreement'));
});

it('stores the redirect URL in session when redirecting to host agreement', function () {
    $this->actingAs($this->user)
        ->get(route('host.eventType.index', ['eventType' => $this->eventType]));

    expect(session('host_agreement_redirect_to'))
        ->toBe(route('host.eventType.index', ['eventType' => $this->eventType]));
});

it('allows access when user has already agreed to host agreement', function () {
    $this->user->update(['agreed_to_host_agreement_date' => now()]);

    $this->actingAs($this->user)
        ->get(route('host.eventType.index', ['eventType' => $this->eventType]))
        ->assertOk();
});

it('works with all existing event types', function () {
    $eventTypes = EventType::all();

    foreach ($eventTypes as $eventType) {
        $this->actingAs($this->user)
            ->get(route('host.eventType.index', ['eventType' => $eventType]))
            ->assertRedirect(route('host.agreement'));
    }
});

it('updates user agreement date when storing host agreement', function () {
    expect($this->user->agreed_to_host_agreement_date)->toBeNull();

    $this->actingAs($this->user)
        ->post(route('host.agreement.store'))
        ->assertRedirect();

    $this->user->refresh();
    expect($this->user->agreed_to_host_agreement_date)->not->toBeNull();
});

it('redirects to stored URL after agreeing to host agreement', function () {
    $anotherEventType = EventType::factory()->create();

    // First, trigger the middleware to store the redirect URL
    $this->actingAs($this->user)
        ->get(route('host.eventType.index', ['eventType' => $anotherEventType]));

    // Then agree to the host agreement
    $this->actingAs($this->user)
        ->post(route('host.agreement.store'))
        ->assertRedirect(route('host.eventType.index', ['eventType' => $anotherEventType]));
});

it('redirects to profile when no redirect URL is stored', function () {
    $this->actingAs($this->user)
        ->post(route('host.agreement.store'))
        ->assertRedirect(route('my.profile'));
});

it('removes redirect URL from session after using it', function () {
    $eventType = EventType::factory()->create();

    // Store a redirect URL in session
    session(['host_agreement_redirect_to' => route('host.eventType.index', ['eventType' => $eventType])]);

    $this->actingAs($this->user)
        ->post(route('host.agreement.store'))
        ->assertRedirect(route('host.eventType.index', ['eventType' => $eventType]));

    // Session should no longer contain the redirect URL
    expect(session('host_agreement_redirect_to'))->toBeNull();
});

it('redirects unauthenticated users appropriately', function () {
    $this->get(route('host.eventType.index', ['eventType' => 'workshop']))
        ->assertRedirect(); // Should redirect to login
});

// Test the hasAgreedToHostAgreement method on the User model
it('correctly identifies users who have agreed to host agreement', function () {
    expect($this->user->hasAgreedToHostAgreement())->toBeFalse();

    $this->user->update(['agreed_to_host_agreement_date' => now()]);

    expect($this->user->hasAgreedToHostAgreement())->toBeTrue();
});
