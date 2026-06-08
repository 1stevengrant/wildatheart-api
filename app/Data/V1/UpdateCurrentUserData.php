<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Illuminate\Contracts\Validation\Validator;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UpdateCurrentUserData extends Data
{
    public function __construct(
        public string|Optional $given_name,
        public string|Optional $family_name,
        public string|Optional|null $phone,
        public string|Optional $city,
        public string|Optional $state,
        public string|Optional $country_code,
        public string|Optional $postal_code,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'given_name' => ['sometimes', 'string', 'max:100'],
            'family_name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'city' => ['sometimes', 'string', 'max:120'],
            'state' => ['sometimes', 'string', 'max:120'],
            'country_code' => ['sometimes', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $allowed = array_keys(self::rules());
            $provided = array_intersect_key($validator->getData(), array_flip($allowed));

            if ($provided === []) {
                $validator->errors()->add(
                    'payload',
                    'At least one updatable field must be provided.'
                );
            }
        });
    }
}
