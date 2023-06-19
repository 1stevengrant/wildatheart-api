<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ShouldNotEndWithFire implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (str_ends_with($value, 'Fire')) {
            $fail('The Fire name should not end with "Fire"');
        }
    }
}
