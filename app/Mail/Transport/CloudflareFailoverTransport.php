<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Toitzi\LaravelCloudflareMail\Exceptions\CloudflareTransportException;

/**
 * Wraps the Cloudflare mail transport so its RuntimeException-based failures
 * are re-thrown as Symfony TransportException. Without this adapter, Symfony's
 * RoundRobinTransport (parent of FailoverTransport) cannot catch the error
 * and the failover never triggers.
 */
final readonly class CloudflareFailoverTransport implements TransportInterface
{
    public function __construct(private TransportInterface $inner) {}

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        try {
            return $this->inner->send($message, $envelope);
        } catch (CloudflareTransportException $e) {
            throw new TransportException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function __toString(): string
    {
        return (string) $this->inner;
    }
}
