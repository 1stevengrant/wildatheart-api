<?php

use Illuminate\Mail\Message;
use Symfony\Component\Mime\Email;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\SentMessage;
use Ghijk\EmailPreview\Models\CapturedEmail;
use Ghijk\EmailPreview\Mail\Transport\AllowlistTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

function recordingTransport(): TransportInterface
{
    return new class implements TransportInterface
    {
        public array $sent = [];

        public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
        {
            $this->sent[] = $message;

            return new SentMessage($message, $envelope ?? Envelope::create($message));
        }

        public function __toString(): string
        {
            return 'recording';
        }
    };
}

function allowlistEmail(array $to, array $cc = [], array $bcc = []): Email
{
    return (new Email)
        ->from('no-reply@mail.wildatheart.org')
        ->to(...$to)
        ->cc(...$cc)
        ->bcc(...$bcc)
        ->subject('Test')
        ->text('Body');
}

it('delivers when every recipient is on an allowed domain', function () {
    $deliver = recordingTransport();
    $capture = recordingTransport();

    (new AllowlistTransport(['wildatheart.org'], $deliver, $capture))
        ->send(allowlistEmail(['jane@wildatheart.org'], cc: ['John@WildAtHeart.org']));

    expect($deliver->sent)->toHaveCount(1);
    expect($capture->sent)->toBeEmpty();
});

it('captures the whole message when any recipient is outside the allowed domains', function (array $to, array $cc, array $bcc) {
    $deliver = recordingTransport();
    $capture = recordingTransport();

    (new AllowlistTransport(['wildatheart.org'], $deliver, $capture))
        ->send(allowlistEmail($to, $cc, $bcc));

    expect($deliver->sent)->toBeEmpty();
    expect($capture->sent)->toHaveCount(1);
})->with([
    'outside to' => [['someone@gmail.com'], [], []],
    'mixed to' => [['jane@wildatheart.org', 'someone@gmail.com'], [], []],
    'outside cc' => [['jane@wildatheart.org'], ['someone@gmail.com'], []],
    'outside bcc' => [['jane@wildatheart.org'], [], ['someone@gmail.com']],
    'subdomain' => [['jane@mail.wildatheart.org'], [], []],
    'lookalike domain' => [['jane@wildatheart.org.evil.com'], [], []],
]);

it('captures everything when no domains are allowed', function () {
    $deliver = recordingTransport();
    $capture = recordingTransport();

    (new AllowlistTransport(['', ' '], $deliver, $capture))
        ->send(allowlistEmail(['jane@wildatheart.org']));

    expect($deliver->sent)->toBeEmpty();
    expect($capture->sent)->toHaveCount(1);
});

it('captures outside mail in the email preview table through the allowlist mailer', function () {
    config([
        'mail.mailers.allowlist.domains' => ['wildatheart.org'],
        'mail.mailers.allowlist.deliver' => 'array',
        'mail.mailers.allowlist.capture' => 'database',
    ]);

    Mail::mailer('allowlist')->raw('Body', fn (Message $message) => $message->to('jane@wildatheart.org')->subject('Staff'));
    Mail::mailer('allowlist')->raw('Body', fn (Message $message) => $message->to('someone@gmail.com')->subject('Outside'));

    expect(CapturedEmail::pluck('subject')->all())->toBe(['Outside']);
});
