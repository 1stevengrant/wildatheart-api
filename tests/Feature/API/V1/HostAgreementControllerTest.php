<?php

use App\Models\User;
use Statamic\Facades\GlobalSet;

function setHostAgreementContent(string $content): void
{
    $globalSet = GlobalSet::findByHandle('local_platform');

    $variables = $globalSet->in('default') ?? $globalSet->makeLocalization('default');

    $variables->set('host_agreement_content', $content)->save();
}

it('returns the host agreement content as html without authentication', function () {
    setHostAgreementContent("## Terms of Use\n\nPlease **read** carefully.");

    $this->getJson('/api/v1/host-agreement')
        ->assertOk()
        ->assertExactJson([
            'content_html' => "<h2>Terms of Use</h2>\n<p>Please <strong>read</strong> carefully.</p>\n",
        ]);
});

it('requires authentication to accept the host agreement', function () {
    $this->postJson('/api/v1/host-agreement')->assertUnauthorized();
});

it('records host agreement acceptance and returns the user payload', function () {
    $this->freezeSecond();

    $user = User::factory()->create([
        'provider_id' => 'auth0|host-agreement-user',
        'agreed_to_host_agreement_date' => null,
    ]);

    mockJwtAuthentication($user);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host-agreement')
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('has_agreed_to_host_agreement', true);

    expect($user->refresh()->agreed_to_host_agreement_date->toDateTimeString())
        ->toBe(now()->toDateTimeString());
});

it('exposes whether the current user has agreed to the host agreement', function (?string $agreedAt, bool $expected) {
    $user = User::factory()->create([
        'provider_id' => 'auth0|me-host-agreement-user',
        'agreed_to_host_agreement_date' => $agreedAt,
    ]);

    mockJwtAuthentication($user);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('has_agreed_to_host_agreement', $expected);
})->with([
    'agreed' => ['2026-01-01 00:00:00', true],
    'not agreed' => [null, false],
]);
