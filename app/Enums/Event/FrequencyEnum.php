<?php

namespace App\Enums\Event;

use Illuminate\Support\Str;

enum FrequencyEnum: string
{
    case BIWEEKLY = 'biweekly';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';

    case QUARTERLY = 'quarterly';
    case VARIES = 'varies';

    /**
     * Get a human-readable label for the frequency
     */
    public function label(): string
    {
        return match ($this) {
            self::BIWEEKLY,
            self::QUARTERLY,
            self::WEEKLY,
            self::MONTHLY,
            self::VARIES => ucwords(Str::slug($this->value)),
        };
    }

    /**
     * The frequencies a Fire schedule can use. `varies` is shown to hosts as Custom.
     *
     * @return array<int, self>
     */
    public static function scheduleCases(): array
    {
        return [self::WEEKLY, self::MONTHLY, self::VARIES];
    }

    public function autoRenews(): bool
    {
        return $this === self::WEEKLY || $this === self::MONTHLY;
    }

    /**
     * Get all frequency options as key-value pairs
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
