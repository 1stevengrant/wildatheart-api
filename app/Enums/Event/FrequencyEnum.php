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
