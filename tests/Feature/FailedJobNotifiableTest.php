<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Queue\Jobs\SyncJob;
use GuzzleHttp\Handler\MockHandler;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\Events\JobFailed;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Notifications\Events\NotificationSent;

function dispatchJobFailed(): void
{
    $payload = json_encode([
        'displayName' => 'App\\Jobs\\ExampleJob',
        'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
        'data' => ['commandName' => 'App\\Jobs\\ExampleJob'],
    ]);

    $job = new SyncJob(app(), $payload, 'sync', 'default');

    event(new JobFailed('sync', $job, new RuntimeException('The job broke')));
}

beforeEach(function () {
    config([
        'mail.default' => 'array',
        'failed-job-monitor.channels' => ['slack', 'mail'],
        'failed-job-monitor.slack.webhook_url' => 'https://hooks.slack.test/services/example',
    ]);

    Exceptions::fake();
});

it('still emails and lets later listeners run when Slack rate limits the notification', function () {
    app()->bind(Client::class, fn () => new Client([
        'handler' => HandlerStack::create(new MockHandler([
            new Response(429, [], '{"retry_after":1,"ok":false,"error":"rate_limited"}'),
        ])),
    ]));

    $channelsSent = [];
    $laterListenerRan = false;

    Event::listen(NotificationSent::class, function (NotificationSent $event) use (&$channelsSent) {
        $channelsSent[] = $event->channel;
    });

    Event::listen(JobFailed::class, function () use (&$laterListenerRan) {
        $laterListenerRan = true;
    });

    dispatchJobFailed();

    expect($channelsSent)->toBe(['mail'])
        ->and($laterListenerRan)->toBeTrue();

    Exceptions::assertReported(ClientException::class);
});

it('notifies every channel when nothing fails', function () {
    app()->bind(Client::class, fn () => new Client([
        'handler' => HandlerStack::create(new MockHandler([new Response(200)])),
    ]));

    $channelsSent = [];

    Event::listen(NotificationSent::class, function (NotificationSent $event) use (&$channelsSent) {
        $channelsSent[] = $event->channel;
    });

    dispatchJobFailed();

    expect($channelsSent)->toBe(['slack', 'mail']);

    Exceptions::assertNothingReported();
});
