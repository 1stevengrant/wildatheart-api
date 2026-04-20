<?php

use App\Models\Issue;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Queue;

it('creates a support ticket and logs the activity', function () {
    Queue::fake();
    $attributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@example.com',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    expect($response->status())->toBe(302)
        ->and(Issue::count())->toBe(1)
        ->and(Issue::first()->name)->toBe('John Doe');

    Queue::assertPushed(LogActivityJob::class);
});

test('a support ticket requires an email address', function () {
    $attributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('email');
});

test('a support ticket requires a valid email address with valid dns and rfs', function () {
    $attributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
        'email' => 'hello@@digitalevangelists',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('email');
});

test('a support ticket requires a valid email address', function () {
    $attributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('email');
});

test('a support ticket requires a given name', function () {
    $attributes = [
        'family_name' => 'Doe',
        'email' => 'steven@example.com',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('given_name');
});

test('a support ticket requires a family name', function () {
    $attributes = [
        'given_name' => 'John',
        'email' => 'steven@example.com',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('family_name');
});

test('a support ticket requires a given name with no more than 255 chars', function () {
    $attributes = [
        'given_name' => str_repeat('a', 256),
        'family_name' => 'Doe',
        'email' => 'steven@example.com',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('given_name');
});

test('a support ticket requires a family name with no more than 255 chars', function () {
    $attributes = [
        'given_name' => 'John',
        'family_name' => str_repeat('a', 256),
        'email' => 'steven@example.com',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('family_name');
});

test('a support ticket requires a subject', function () {
    $attributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@example.com',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('subject');
});

test('a support ticket requires a subject with no more than 255 chars', function () {
    $attributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@example.com',
        'subject' => str_repeat('a', 256),
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('subject');
});

test('a support ticket requires a description', function () {
    $attributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@example.com',
        'subject' => 'Test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('description');

});

test('the support store route exists', function () {
    $response = $this->post(route('support.store'));
    $response->assertStatus(302);
});
