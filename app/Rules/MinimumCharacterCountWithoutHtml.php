<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinimumCharacterCountWithoutHtml implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        protected int $minCharacters = 10
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (mb_strlen(strip_tags($value)) < $this->minCharacters) {
            $fail("The :attribute must be at least {$this->minCharacters} characters.");
        }
    }
}
