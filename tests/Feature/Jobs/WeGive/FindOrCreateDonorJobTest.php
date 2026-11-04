<?php

use App\Models\User;
use App\Jobs\LogActivityJob;
use Saloon\Http\Faking\MockClient;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Saloon\Http\Faking\MockResponse;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use Saloon\Exceptions\Request\RequestException;
use App\Http\Integrations\WeGive\WeGiveConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\WeGive\Requests\FindOrCreateDonorsRequest;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    config(['services.wegive.api_key' => 'test-key']);

    $this->user = User::factory()->create(['wegive_donor_id' => null]);
    $this->job = (new FindOrCreateDonorJob($this->user))->withFakeQueueInteractions();
});

afterEach(function () {
    MockClient::destroyGlobal();
});

it('stores the donor id from a successful response', function () {
    MockClient::global([
        FindOrCreateDonorsRequest::class => MockResponse::make(['data' => ['id' => 'donor-123']]),
    ]);

    $this->job->handle();

    expect($this->user->fresh()->wegive_donor_id)->toBe('donor-123');

    $this->job->assertNotFailed();
});

it('retries a server error with a non JSON body and logs the body', function () {
    MockClient::global([
        FindOrCreateDonorsRequest::class => MockResponse::make('<html>Bad Gateway</html>', 502),
    ]);

    expect(fn () => $this->job->handle())->toThrow(RequestException::class);

    $this->job->assertNotFailed();

    expect($this->user->fresh()->wegive_donor_id)->toBeNull();

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $log) {
        return $log->logName === WeGiveConnector::ERROR_LOG_NAME
            && $log->description === 'Error creating donor'
            && $log->properties['response'] === '<html>Bad Gateway</html>';
    });
});

it('fails permanently on a validation error instead of reporting a created donor', function () {
    MockClient::global([
        FindOrCreateDonorsRequest::class => MockResponse::make(['message' => 'The email is invalid'], 422),
    ]);

    expect(fn () => $this->job->handle())->toThrow(RequestException::class);

    $this->job->assertFailed();

    Bus::assertNotDispatched(LogActivityJob::class, fn (LogActivityJob $log) => $log->description === 'Created donor');
});

it('retries when rate limited', function () {
    MockClient::global([
        FindOrCreateDonorsRequest::class => MockResponse::make(['message' => 'Too many requests'], 429),
    ]);

    expect(fn () => $this->job->handle())->toThrow(RequestException::class);

    $this->job->assertNotFailed();
});

it('retries a connection failure and logs the real error', function () {
    MockClient::global([
        FindOrCreateDonorsRequest::class => MockResponse::make()->throw(
            fn ($pendingRequest) => new FatalRequestException(new Exception('Connection timed out'), $pendingRequest),
        ),
    ]);

    expect(fn () => $this->job->handle())->toThrow(FatalRequestException::class, 'Connection timed out');

    $this->job->assertNotFailed();

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $log) {
        return $log->description === 'Error creating donor'
            && $log->properties['error'] === 'Connection timed out'
            && $log->properties['response'] === null;
    });
});

it('retries a successful response with a non JSON body and logs the body', function () {
    MockClient::global([
        FindOrCreateDonorsRequest::class => MockResponse::make('<html>Maintenance</html>'),
    ]);

    expect(fn () => $this->job->handle())->toThrow(JsonException::class);

    $this->job->assertNotFailed();

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $log) {
        return $log->description === 'Invalid JSON response when creating donor'
            && $log->properties['response'] === '<html>Maintenance</html>';
    });
});

it('logs the response without retrying when a successful response has no donor id', function () {
    MockClient::global([
        FindOrCreateDonorsRequest::class => MockResponse::make(['data' => []]),
    ]);

    $this->job->handle();

    $this->job->assertNotFailed();

    expect($this->user->fresh()->wegive_donor_id)->toBeNull();

    Bus::assertNotDispatched(LogActivityJob::class, fn (LogActivityJob $log) => $log->description === 'Created donor');

    Bus::assertDispatched(LogActivityJob::class, function (LogActivityJob $log) {
        return $log->logName === WeGiveConnector::ERROR_LOG_NAME
            && $log->description === 'Donor not found or created'
            && $log->properties['response'] === '{"data":[]}';
    });
});
