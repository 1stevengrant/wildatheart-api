<?php

use App\Http\Livewire\ReportIssue;
use App\Jobs\ReportIssueToSlack;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('report issue component renders', function () {
    livewire(ReportIssue::class)
        ->assertStatus(200);
});

test('it creates a zendesk support ticket in the database', function () {

    Queue::fake();

    $payload = [
        'name' => 'Test User',
        'email' => 'steven@servant.io',
        'subject' => 'Test Subject',
        'description' => 'Test Description',
    ];

    livewire(ReportIssue::class)
        ->set('name', $payload['name'])
        ->set('email', $payload['email'])
        ->set('subject', $payload['subject'])
        ->set('description', $payload['description'])
        ->call('handle')
        ->assertEmitted('issueReported');

    Queue::assertPushed(ReportIssueToSlack::class);

    $this->assertDatabaseHas('issues', [
        'name' => $payload['name'],
        'email' => $payload['email'],
        'subject' => 'Fires Feedback: '.$payload['subject'],
        'description' => $payload['description'],
    ]);
});

it('needs the email address', function () {
    $payload = [
        'name' => 'Test User',
        'email' => '',
        'subject' => 'Test Subject',
        'description' => 'Test Description',
    ];

    livewire(ReportIssue::class)
        ->set('name', $payload['name'])
        ->set('email', $payload['email'])
        ->set('subject', $payload['subject'])
        ->set('description', $payload['description'])
        ->call('handle')
        ->assertHasErrors(['email' => 'required']);
});

it('needs the email address to be an email address', function () {
    $payload = [
        'name' => 'Test User',
        'email' => 'sts',
        'subject' => 'Test Subject',
        'description' => 'Test Description',
    ];

    livewire(ReportIssue::class)
        ->set('name', $payload['name'])
        ->set('email', $payload['email'])
        ->set('subject', $payload['subject'])
        ->set('description', $payload['description'])
        ->call('handle')
        ->assertHasErrors(['email' => 'email']);
});
