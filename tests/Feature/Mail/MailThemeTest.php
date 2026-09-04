<?php

use App\Models\Event;
use App\Mail\HostInvitationMail;
use App\Mail\Transport\CloudflareFailoverTransport;

/**
 * Guards the Wild at Heart mail theme in resources/views/vendor/mail.
 *
 * These views are published from Laravel, so a future `vendor:publish --force`
 * or an upgrade that reintroduces the stock templates would silently revert the
 * branding. Asserting on the rendered output catches that.
 */
function renderBrandedEmail(): string
{
    $event = Event::factory()->fires()->create();

    return (new HostInvitationMail($event, 'Aslan'))->render();
}

test('it renders the Wild at Heart logo in the header', function () {
    $html = renderBrandedEmail();

    expect($html)
        ->toContain('img/email-logo.png')
        ->toContain('alt="Wild at Heart"');
});

test('it paints the page and card with the brand dark palette', function () {
    $html = renderBrandedEmail();

    expect($html)
        ->toContain('background-color: #010101')
        ->toContain('background-color: #0D151A')
        ->toContain('bgcolor="#010101"')
        ->toContain('bgcolor="#0D151A"');
});

test('it declares a dark color scheme so clients do not invert the design', function () {
    $html = renderBrandedEmail();

    expect($html)
        ->toContain('<meta name="color-scheme" content="dark">')
        ->not->toContain('content="light"');
});

test('it fills primary buttons with the brand orange and a legible label', function () {
    $html = renderBrandedEmail();

    preg_match('/<a[^>]*class="button button-primary"[^>]*style="([^"]*)"/', $html, $matches);

    expect($matches[1] ?? '')
        ->toContain('background-color: #964300')
        ->toContain('color: #ffffff');
});

test('it does not break link text mid-word', function () {
    expect(renderBrandedEmail())->not->toContain('word-break: break-all');
});

it('resolves the Cloudflare transport through the Laravel mail manager', function (): void {
    config()->set([
        'mail.mailers.cloudflare.account_id' => 'test-account',
        'mail.mailers.cloudflare.api_token' => 'test-token',
    ]);

    $transport = app('mail.manager')
        ->mailer('cloudflare')
        ->getSymfonyTransport();

    expect($transport)->toBeInstanceOf(CloudflareFailoverTransport::class);
});
