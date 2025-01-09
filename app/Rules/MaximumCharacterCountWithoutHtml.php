<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaximumCharacterCountWithoutHtml implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        protected int $maxCharacters = 10
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (mb_strlen(strip_tags($value)) > $this->maxCharacters) {
            $fail("The :attribute must be at less than {$this->maxCharacters} characters.");
        }
    }
}
