<?php

namespace App\Models;

use App\Enums\EmailLogStatus;
use Illuminate\Support\Number;
use Database\Factories\EmailLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailLog extends BaseModel
{
    /** @use HasFactory<EmailLogFactory> */
    use HasFactory, HasRunwayResource, MassPrunable;

    public const RETENTION_DAYS = 90;

    protected function casts(): array
    {
        return [
            'delivery_status' => EmailLogStatus::class,
            'attachments' => 'array',
        ];
    }

    /** @return Builder<EmailLog> */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(self::RETENTION_DAYS));
    }

    protected function mailableName(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->mailable ? class_basename($this->mailable) : null,
        );
    }

    protected function attachmentsSummary(): Attribute
    {
        return Attribute::get(
            fn (): string => collect($this->attachments)
                ->map(fn (array $attachment): string => $attachment['name'] . ' (' . Number::fileSize($attachment['size']) . ')')
                ->implode(', '),
        );
    }
}
