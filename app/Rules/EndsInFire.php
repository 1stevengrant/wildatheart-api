<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EndsInFire implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! str_ends_with($value, 'fire')) {
            $fail('The Fire name must end with "Fire"');
        }
    }
}
