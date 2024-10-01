<?php

namespace App\DTO;

class GoogleSheetDTO
{
    public function __construct(
        public string $webhookUrl,
        public string $eventId,
        public string $eventType,
        public string $email,
        public ?string $quantity,
        public string $first_name,
        public string $last_name,
        public string $address_1,
        public ?string $address_2,
        public string $city,
        public ?string $state,
        public string $zip,
        public string $country,
        public string $date
    ) {}
}
