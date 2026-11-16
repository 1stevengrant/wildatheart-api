<?php

use App\Models\User;
use App\Models\Event;

use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->coHost = User::factory()->create();
    $this->event = Event::factory()->fires()->approved()->for($this->owner)->create();
    $this->event->managers()->attach($this->coHost->id);

    Mail::fake();
    Bus::fake();
});

it('flashes a success message after inviting a host', function () {
    actingAs($this->coHost)
        ->from('/previous')
        ->post(route('events.managers.store', $this->event), ['email' => 'new@example.com'])
        ->assertRedirect('/previous')
        ->assertSessionHas('notifications', [
            'message' => 'An invitation has been sent to new@example.com. They will become a host once they create an account and accept.',
            'type' => 'success',
        ]);
});

it('flashes the domain failure when the invitation is rejected', function () {
    actingAs($this->owner)
        ->from('/previous')
        ->post(route('events.managers.store', $this->event), ['email' => $this->coHost->email])
        ->assertRedirect('/previous')
        ->assertSessionHas('notifications', [
            'message' => 'This user is already a host for this event.',
            'type' => 'error',
        ]);
});

it('forbids users who cannot manage the event', function () {
    actingAs(User::factory()->create())
        ->post(route('events.managers.store', $this->event), ['email' => 'new@example.com'])
        ->assertForbidden();

    expect(EventManagerInvitation::query()->count())->toBe(0);
});

it('flashes a success message after removing a host', function () {
    actingAs($this->owner)
        ->from('/previous')
        ->delete(route('events.managers.destroy', [$this->event, $this->coHost->id]))
        ->assertRedirect('/previous')
        ->assertSessionHas('notifications', [
            'message' => "{$this->coHost->fullname} has been removed as a host.",
            'type' => 'success',
        ]);
});

it('flashes the domain failure when removal is rejected', function () {
    actingAs($this->coHost)
        ->from('/previous')
        ->delete(route('events.managers.destroy', [$this->event, $this->owner->id]))
        ->assertRedirect('/previous')
        ->assertSessionHas('notifications', [
            'message' => 'Cannot remove the event owner.',
            'type' => 'error',
        ]);
});
