<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class MailingAddressData extends Data
{
    public function __construct(
        public readonly string $given_name,
        public readonly string $family_name,
        public readonly string $street_address,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zip,
        public readonly string $country,
        public readonly ?string $company = null,
        public readonly ?string $address2 = null,
        public readonly ?string $phone = null,
    ) {}
}
