<?php

namespace Ghijk\EmailPreview\Mail\Transport;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;

class AllowlistTransport implements TransportInterface
{
    /** @var array<int, string> */
    protected array $domains;

    /**
     * @param  array<int, string>  $domains
     */
    public function __construct(
        array $domains,
        protected TransportInterface $deliver,
        protected TransportInterface $capture,
    ) {
        $this->domains = array_values(array_filter(array_map(
            fn (string $domain) => mb_strtolower(mb_trim($domain)),
            $domains,
        )));
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $envelope ??= Envelope::create($message);

        if (! $this->allRecipientsAllowed($envelope->getRecipients())) {
            return $this->capture->send($message, $envelope);
        }

        return $this->deliver->send($message, $envelope);
    }

    /** @param array<int, Address> $recipients */
    protected function allRecipientsAllowed(array $recipients): bool
    {
        if ($this->domains === []) {
            return false;
        }

        foreach ($recipients as $recipient) {
            if (! $this->isAllowed($recipient)) {
                return false;
            }
        }

        return true;
    }

    protected function isAllowed(Address $address): bool
    {
        $domain = mb_strtolower(mb_substr(mb_strrchr($address->getAddress(), '@'), 1));

        return in_array($domain, $this->domains, true);
    }

    public function __toString(): string
    {
        return "allowlist({$this->deliver}|{$this->capture})";
    }
}
