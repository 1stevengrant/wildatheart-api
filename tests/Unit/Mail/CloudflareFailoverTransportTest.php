<?php

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use App\Mail\Transport\CloudflareFailoverTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Toitzi\LaravelCloudflareMail\Exceptions\CloudflareTransportException;

it('rethrows CloudflareTransportException as Symfony TransportException so failover triggers', function (): void {
    $inner = Mockery::mock(TransportInterface::class);
    $inner->shouldReceive('send')->once()->andThrow(
        new CloudflareTransportException('Cloudflare mail API request failed with status [400]: [10202] email.invalid', 400)
    );

    $adapter = new CloudflareFailoverTransport($inner);

    $message = (new Email)->from('a@example.com')->to('b@example.com')->subject('x')->text('y');

    expect(fn () => $adapter->send($message))
        ->toThrow(TransportException::class, '[10202] email.invalid');
});

it('passes successful sends through unchanged', function (): void {
    $message = (new Email)->from('a@example.com')->to('b@example.com')->subject('x')->text('y');
    $sent = new SentMessage($message, Envelope::create($message));

    $inner = Mockery::mock(TransportInterface::class);
    $inner->shouldReceive('send')->once()->andReturn($sent);

    $adapter = new CloudflareFailoverTransport($inner);

    expect($adapter->send($message))->toBe($sent);
});

it('exposes a TransportInterface for use inside FailoverTransport', function (): void {
    $inner = Mockery::mock(TransportInterface::class);

    expect(new CloudflareFailoverTransport($inner))->toBeInstanceOf(TransportInterface::class);
});
