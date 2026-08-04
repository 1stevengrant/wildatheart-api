<?php

use App\Models\User;
use App\Jobs\LogActivityJob;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Integrations\WeGive\WeGiveConnector;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
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
