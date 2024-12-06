<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ShouldNotEndWithFire implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (str_ends_with(mb_strtolower($value), 'fire')) {
            $fail('The Fire name should not end with "Fire"');
        }
    }
}
