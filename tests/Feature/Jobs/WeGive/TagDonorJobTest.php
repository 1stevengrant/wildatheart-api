<?php

use App\Models\User;
use App\Jobs\LogActivityJob;
use App\Jobs\WeGive\TagDonorJob;
use Saloon\Http\Faking\MockClient;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\TagDonorsRequest;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    config(['services.wegive.api_key' => 'test-key']);
});

afterEach(function () {
    MockClient::destroyGlobal();
});

it('does not call WeGive when no tag is configured', function () {
    Http::preventStrayRequests();

    $user = User::factory()->create(['wegive_donor_id' => 'donor-123']);

    (new TagDonorJob(taggable: $user, weGiveTagId: null))->handle();

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) use ($user) {
        return $job->logName === WeGiveConnector::ERROR_LOG_NAME
            && $job->description === 'Missing required tagging data'
            && $job->properties['taggable_id'] === $user->id
            && $job->properties['tag'] === null;
    });
});

it('does not call WeGive when the taggable has no donor id', function () {
    Http::preventStrayRequests();

    $user = User::factory()->create(['wegive_donor_id' => null]);

    (new TagDonorJob(taggable: $user, weGiveTagId: '239'))->handle();

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $job) use ($user) {
        return $job->logName === WeGiveConnector::ERROR_LOG_NAME
            && $job->description === 'Missing required tagging data'
            && $job->properties['taggable_id'] === $user->id;
    });
});

it('retries a server error with a non JSON body and logs it once', function () {
    MockClient::global([
        TagDonorsRequest::class => MockResponse::make('<html>Bad Gateway</html>', 502),
    ]);

    $user = User::factory()->create(['wegive_donor_id' => 'donor-123']);
    $job = (new TagDonorJob(taggable: $user, weGiveTagId: '239'))->withFakeQueueInteractions();

    expect(fn () => $job->handle())->toThrow(RequestException::class);

    $job->assertNotFailed();

    Bus::assertDispatchedTimes(LogActivityJob::class, 1);

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $log) {
        return $log->description === 'Error tagging donor'
            && $log->properties['response'] === '<html>Bad Gateway</html>';
    });
});

it('fails permanently on a client error', function () {
    MockClient::global([
        TagDonorsRequest::class => MockResponse::make(['message' => 'Not found'], 404),
    ]);

    $user = User::factory()->create(['wegive_donor_id' => 'donor-123']);
    $job = (new TagDonorJob(taggable: $user, weGiveTagId: '239'))->withFakeQueueInteractions();

    expect(fn () => $job->handle())->toThrow(RequestException::class);

    $job->assertFailed();

    Bus::assertNotDispatched(LogActivityJob::class, fn (LogActivityJob $log) => $log->description === 'Tagged donor');
});

it('retries a connection failure and logs the real error', function () {
    MockClient::global([
        TagDonorsRequest::class => MockResponse::make()->throw(
            fn ($pendingRequest) => new FatalRequestException(new Exception('Connection timed out'), $pendingRequest),
        ),
    ]);

    $user = User::factory()->create(['wegive_donor_id' => 'donor-123']);
    $job = (new TagDonorJob(taggable: $user, weGiveTagId: '239'))->withFakeQueueInteractions();

    expect(fn () => $job->handle())->toThrow(FatalRequestException::class, 'Connection timed out');

    $job->assertNotFailed();

    Bus::assertDispatched(LogActivityJob::class, fn (LogActivityJob $log) => $log->properties['error'] === 'Connection timed out');
});

it('treats a successful response with a non JSON body as tagged', function () {
    MockClient::global([
        TagDonorsRequest::class => MockResponse::make('OK'),
    ]);

    $user = User::factory()->create(['wegive_donor_id' => 'donor-123']);
    $job = (new TagDonorJob(taggable: $user, weGiveTagId: '239'))->withFakeQueueInteractions();

    $job->handle();

    $job->assertNotFailed();

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $log) {
        return $log->description === 'Tagged donor'
            && $log->properties['response'] === 'OK';
    });
});
