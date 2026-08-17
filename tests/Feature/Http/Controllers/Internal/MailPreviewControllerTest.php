<?php

use App\Models\User;
use App\Models\Event;

function superAdmin(): User
{
    return User::factory()->create(['super' => true]);
}

test('it lists the available mail previews', function () {
    $this->actingAs(superAdmin())
        ->get(route('internal.mail-previews.index'))
        ->assertOk()
        ->assertSee('Mail previews')
        ->assertSee('Host welcome (sent when an event is created)')
        ->assertSee('Fire lifecycle');
});

test('it renders a preview with the branded theme', function () {
    Event::factory()->fires()->create();

    $response = $this->actingAs(superAdmin())
        ->get(route('internal.mail-previews.show', 'new-event-created--fires'));

    $response->assertOk();

    expect($response->getContent())
        ->toContain('img/email-logo.png')
        ->toContain('background-color: #0D151A');
});

test('it fails loudly when a preview cannot be built', function () {
    // No event of this type exists, so the sample cannot be resolved.
    $this->actingAs(superAdmin())
        ->get(route('internal.mail-previews.show', 'new-event-created--survival-school'))
        ->assertStatus(500)
        ->assertSee('could not be rendered');
});

test('it 404s for an unknown preview', function () {
    $this->actingAs(superAdmin())
        ->get(route('internal.mail-previews.show', 'does-not-exist'))
        ->assertNotFound();
});

test('it does not persist anything while rendering previews', function () {
    Event::factory()->fires()->create();

    $before = Event::count();

    $this->actingAs(superAdmin())
        ->get(route('internal.mail-previews.show', 'new-event-created--fires'))
        ->assertOk();

    expect(Event::count())->toBe($before);
});

test('it forbids users who are not super admins', function () {
    $this->actingAs(User::factory()->create(['super' => false]))
        ->get(route('internal.mail-previews.index'))
        ->assertForbidden();
});

test('it does not expose previews to guests', function () {
    $this->get(route('internal.mail-previews.index'))->assertRedirect();
});
