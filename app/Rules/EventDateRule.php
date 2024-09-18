<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EventDateRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $fail("The $attribute must be in the format YYYY-MM-DD.");
        }
    }
}
