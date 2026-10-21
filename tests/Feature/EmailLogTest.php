<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\EmailLog;
use App\Mail\ReportMailable;
use App\Enums\EmailLogStatus;
use App\Models\EmailTemplate;
use App\Traits\LogsFailedDelivery;
use Illuminate\Support\Facades\DB;
use StatamicRadPack\Runway\Runway;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Mail\Event\AttendeeRegistered;
use App\Listeners\LogSentEmailListener;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event as EventFacade;
use App\Fieldtypes\EmailLogStatus as EmailLogStatusFieldtype;

beforeEach(function () {
    Queue::fake();

    EmailTemplate::where('slug', 'attendee-registered')->update([
        'subject' => '[attendee_name] wants to join',
        'body' => 'Hi [host_name], [attendee_name] has expressed interest in your event.',
    ]);
});

function attendeeRegisteredMailable(): AttendeeRegistered
{
    $host = User::factory()->create(['given_name' => 'John']);
    $event = Event::factory()->fires()->approved()->for($host)->create();
    $attendee = Attendee::factory()->for($event)->create(['given_name' => 'Jane', 'family_name' => 'Doe']);

    return (new AttendeeRegistered($event, $attendee))->to('host@example.com', 'John Host');
}

it('listens for sent emails', function () {
    EventFacade::fake();

    EventFacade::assertListening(MessageSent::class, LogSentEmailListener::class);
});

it('logs failed deliveries for every queued mailable', function () {
    $queuedMailables = collect(File::allFiles(app_path('Mail')))
        ->map(fn (SplFileInfo $file): string => 'App\\Mail\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname()))
        ->filter(fn (string $class): bool => is_subclass_of($class, ShouldQueue::class))
        ->filter(fn (string $class): bool => ! (new ReflectionClass($class))->isAbstract());

    expect($queuedMailables)->not->toBeEmpty();

    $queuedMailables->each(fn (string $class) => expect(class_uses_recursive($class))->toHaveKey(LogsFailedDelivery::class));
});

it('logs a sent email', function () {
    $mailable = attendeeRegisteredMailable();

    $mailable->send(Mail::mailer('array'));

    $log = EmailLog::sole();

    expect($log->delivery_status)->toBe(EmailLogStatus::Sent)
        ->and($log->mailable)->toBe(AttendeeRegistered::class)
        ->and($log->mailable_name)->toBe('AttendeeRegistered')
        ->and($log->subject)->toBe('Jane Doe wants to join')
        ->and($log->to)->toBe('"John Host" <host@example.com>')
        ->and($log->html_body)->toContain('Hi John, Jane has expressed interest in your event.')
        ->and($log->mailer)->toBe('array')
        ->and($log->message_id)->not->toBeNull()
        ->and($log->event_id)->toBe($mailable->event->id)
        ->and($log->error)->toBeNull();
});

it('stores attachment metadata without the attachment contents', function () {
    Storage::fake();
    Storage::put('report.csv', "Name,Email\nJohn,john@example.com");

    (new ReportMailable('01/15/2024', Storage::path('report.csv'), 'Hosts'))
        ->to('reports@example.com')
        ->send(Mail::mailer('array'));

    $log = EmailLog::sole();

    expect($log->attachments)->toHaveCount(1)
        ->and($log->attachments[0])->toHaveKeys(['name', 'content_type', 'size'])
        ->and($log->attachments[0])->not->toHaveKey('body')
        ->and($log->attachments_summary)->toContain('.csv')
        ->and($log->event_id)->toBeNull();
});

it('still sends the email when logging fails', function () {
    Exceptions::fake();

    EmailLog::creating(fn () => throw new RuntimeException('Logging failed'));

    attendeeRegisteredMailable()->send(Mail::mailer('array'));

    expect(Mail::mailer('array')->getSymfonyTransport()->messages())->toHaveCount(1)
        ->and(EmailLog::count())->toBe(0);

    Exceptions::assertReported(RuntimeException::class);
});

it('logs an email whose queued job failed', function () {
    $mailable = attendeeRegisteredMailable();

    (new SendQueuedMailable($mailable))->failed(new RuntimeException('Cloudflare and SendGrid are down'));

    $log = EmailLog::sole();

    expect($log->delivery_status)->toBe(EmailLogStatus::Failed)
        ->and($log->mailable)->toBe(AttendeeRegistered::class)
        ->and($log->subject)->toBe('Jane Doe wants to join')
        ->and($log->to)->toBe('"John Host" <host@example.com>')
        ->and($log->html_body)->toBeNull()
        ->and($log->event_id)->toBe($mailable->event->id)
        ->and($log->error)->toBe('Cloudflare and SendGrid are down');
});

it('prunes email logs older than 90 days', function () {
    $expired = EmailLog::factory()->create(['created_at' => now()->subDays(91)]);
    $retained = EmailLog::factory()->create(['created_at' => now()->subDays(89)]);

    $this->artisan('model:prune', ['--model' => [EmailLog::class]])->assertSuccessful();

    expect(EmailLog::find($expired->id))->toBeNull()
        ->and(EmailLog::find($retained->id))->not->toBeNull();
});

it('configures the email log as a read only runway resource', function () {
    $resource = Runway::findResource('email_log');

    expect($resource->model())->toBeInstanceOf(EmailLog::class)
        ->and($resource->name())->toBe('Email Log')
        ->and($resource->titleField())->toBe('subject')
        ->and($resource->readOnly())->toBeTrue()
        ->and($resource->duplicatable())->toBeFalse()
        ->and($resource->orderBy())->toBe('created_at')
        ->and($resource->orderByDirection())->toBe('DESC')
        ->and($resource->blueprint()->field('delivery_status')->fieldtype())->toBeInstanceOf(EmailLogStatusFieldtype::class)
        ->and($resource->blueprint()->fields()->has('html_body'))->toBeTrue();
});

it('shows the email log to super users', function () {
    $log = EmailLog::factory()->create();

    $this->actingAs(User::factory()->create(['super' => true]));

    $this->get(cp_route('runway.index', ['resource' => 'email_log']))
        ->assertOk();

    $this->get(cp_route('runway.listing-api', ['resource' => 'email_log']))
        ->assertOk()
        ->assertJsonPath('data.0.subject', $log->subject)
        ->assertJsonPath('data.0.delivery_status', 'sent')
        ->assertJsonMissingPath('data.0.html_body');

    $this->get(cp_route('runway.edit', ['resource' => 'email_log', 'model' => $log->id]))
        ->assertOk();
});

it('does not expose the email log through the content api', function () {
    EmailLog::factory()->create();

    $this->getJson('/api/content/runway/email_log')->assertNotFound();
});

it('hides the email log from other control panel users', function () {
    $user = User::factory()->create(['super' => false]);

    DB::table('role_user')->insert([
        'user_id' => $user->id,
        'role_id' => 'blog_editor',
    ]);

    $this->actingAs($user)
        ->get(cp_route('runway.index', ['resource' => 'email_log']))
        ->assertRedirect(route('login'));
});
