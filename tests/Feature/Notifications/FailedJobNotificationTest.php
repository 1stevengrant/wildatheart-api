<?php

use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Spatie\FailedJobMonitor\Notifiable;
use App\Notifications\FailedJobNotification;
use Illuminate\Notifications\SendQueuedNotifications;

function failedJobEvent(string $displayName = 'App\Jobs\SomeJob'): JobFailed
{
    $job = new SyncJob(app(), json_encode([
        'displayName' => $displayName,
        'job' => 'Illuminate\Queue\CallQueuedHandler@call',
        'data' => ['command' => 'serialized'],
    ]), 'sync', 'default');

    return new JobFailed('sync', $job, new RuntimeException('Boom'));
}

it('queues the failure notification instead of sending it inside the worker', function (): void {
    Queue::fake();

    event(failedJobEvent());

    Queue::assertPushed(
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job): bool => $job->notification instanceof FailedJobNotification
            && $job->notification->jobName === 'App\Jobs\SomeJob'
            && $job->notification->exceptionMessage === 'Boom'
    );
});

it('survives serialization for the queue', function (): void {
    $notification = unserialize(serialize((new FailedJobNotification)->setEvent(failedJobEvent())));

    expect($notification->jobName)->toBe('App\Jobs\SomeJob')
        ->and($notification->exceptionMessage)->toBe('Boom')
        ->and($notification->jobBody)->toContain('serialized')
        ->and($notification->exceptionTrace)->not->toBeEmpty();
});

it('does not notify about its own failed deliveries', function (): void {
    $notifiable = new Notifiable;

    expect((new FailedJobNotification)->setEvent(failedJobEvent())->shouldSend($notifiable, 'slack'))->toBeTrue()
        ->and((new FailedJobNotification)->setEvent(failedJobEvent(FailedJobNotification::class))->shouldSend($notifiable, 'slack'))->toBeFalse();
});

it('includes the failure details in the slack message', function (): void {
    $message = (new FailedJobNotification)->setEvent(failedJobEvent())->toSlack();

    expect($message->attachments[0]->fields)
        ->toMatchArray([
            'Exception message' => 'Boom',
            'Job class' => 'App\Jobs\SomeJob',
        ]);
});
