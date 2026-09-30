<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Ghijk\EmailPreview\Models\CapturedEmail;

function capturedEmailsAdmin(): User
{
    return User::factory()->create(['super' => true]);
}

test('the index reports the active mailer configuration', function () {
    config(['mail.default' => 'database']);

    $this->actingAs(capturedEmailsAdmin())
        ->get(route('internal.captured-emails.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CapturedEmails/Index')
            ->where('mailConfig.mailer', 'database')
            ->where('mailConfig.transport', 'database')
            ->where('mailConfig.captures', true)
            ->where('testResult', null));
});

test('sending a test email through the database mailer captures it', function () {
    config(['mail.default' => 'database']);
    $admin = capturedEmailsAdmin();

    $response = $this->actingAs($admin)
        ->post(route('internal.captured-emails.test'))
        ->assertRedirect(route('internal.captured-emails.index'));

    $captured = CapturedEmail::sole();

    expect($captured->to)->toBe([$admin->email])
        ->and($captured->subject)->toStartWith('Test email [');

    $response->assertSessionHas('email-preview.test_result', [
        'status' => 'captured',
        'message' => "Test email to {$admin->email} was captured successfully.",
        'uuid' => $captured->uuid,
    ]);
});

test('sending a test email through a non-capturing mailer reports it was not captured', function () {
    config(['mail.default' => 'array']);

    $this->actingAs(capturedEmailsAdmin())
        ->post(route('internal.captured-emails.test'))
        ->assertSessionHas('email-preview.test_result.status', 'not_captured');

    expect(CapturedEmail::count())->toBe(0);
});

test('a failing mailer is reported', function () {
    Mail::shouldReceive('to->send')->andThrow(new RuntimeException('Connection refused'));

    $this->actingAs(capturedEmailsAdmin())
        ->post(route('internal.captured-emails.test'))
        ->assertSessionHas('email-preview.test_result.status', 'failed')
        ->assertSessionHas('email-preview.test_result.message', fn (string $message) => str_contains($message, 'Connection refused'));
});

test('only super admins can send test emails', function () {
    $this->actingAs(User::factory()->create(['super' => false]))
        ->post(route('internal.captured-emails.test'))
        ->assertForbidden();

    expect(CapturedEmail::count())->toBe(0);
});
