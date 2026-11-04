<?php

namespace App\Support;

use Symfony\Component\Mime\Address;

class EmailAddresses
{
    /** @param array<int, Address> $addresses */
    public static function format(array $addresses): ?string
    {
        if ($addresses === []) {
            return null;
        }

        return collect($addresses)
            ->map(fn (Address $address): string => $address->toString())
            ->implode(', ');
    }

    /** @param array<int, array{name: ?string, address: string}> $recipients */
    public static function formatRecipients(array $recipients): ?string
    {
        return self::format(array_map(
            fn (array $recipient): Address => new Address($recipient['address'], $recipient['name'] ?? ''),
            $recipients,
        ));
    }
}
