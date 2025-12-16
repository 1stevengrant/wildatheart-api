<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\EventOwnershipTransfer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventOwnershipTransferData extends Data
{
    public function __construct(
        public string $id,
        public string $email,
        public string $transferred_by_name,
        public bool $is_pending,
    ) {}

    public static function fromModel(EventOwnershipTransfer $transfer): self
    {
        return new self(
            id: $transfer->id,
            email: $transfer->email,
            transferred_by_name: $transfer->transferredByUser->fullname,
            is_pending: $transfer->isPending(),
        );
    }
}
