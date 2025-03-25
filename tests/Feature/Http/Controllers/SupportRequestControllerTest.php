<?php

use Illuminate\Support\Facades\Queue;

it('creates a support ticket and logs the activity', function () {
    Queue::fake();
    $attributes = [
        'name' => 'John Doe',
        'email' => 'steven@ghijk.co.uk',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    expect($response->status())->toBe(302)
        ->and(\App\Models\Issue::count())->toBe(1);

    Queue::assertPushed(\App\Jobs\LogActivityJob::class);
});

test('a support ticket requires an email address', function () {
    $attributes = [
        'name' => 'John Doe',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('email');
});

test('a support ticket requires a valid email address with valid dns and rfs', function () {
    $attributes = [
        'name' => 'John Doe',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
        'email' => 'hello@digitalevangelistnet',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('email');
});

test('a support ticket requires a valid email address', function () {
    $attributes = [
        'name' => 'John Doe',
        'email' => 'steven',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('email');
});

test('a support ticket requires a name', function () {
    $attributes = [
        'email' => 'steven@ghijk.co.uk',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('name');
});

test('a support ticket requires a name with no more than 255 chars', function () {
    $attributes = [
        'name' => str_repeat('a', 256),
        'email' => 'steven@ghijk.co.uk',
        'subject' => 'Test feedback',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);
    $response->assertSessionHasErrors('name');
});

test('a support ticket requires a subject', function () {
    $attributes = [
        'name' => 'John Doe',
        'email' => 'steven@ghijk.co.uk',
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('subject');
});

test('a support ticket requires a subject with no more than 255 chars', function () {
    $attributes = [
        'name' => 'John Doe',
        'email' => 'steven@ghijk.co.uk',
        'subject' => str_repeat('a', 256),
        'description' => 'This is a test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('subject');
});

test('a support ticket requires a description', function () {
    $attributes = [
        'name' => 'John Doe',
        'email' => 'steven@ghijk.co.uk',
        'subject' => 'Test feedback',
    ];

    $response = $this->post(route('support.store'), $attributes);

    $response->assertSessionHasErrors('description');

});

test('the support store route exists', function () {
    $response = $this->post(route('support.store'));
    $response->assertStatus(302);
});
