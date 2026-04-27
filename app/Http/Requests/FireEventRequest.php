<?php

namespace App\Http\Requests;

use App\Rules\DoesntContainEmojis;
use App\Rules\ShouldNotEndWithFire;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasLocationRules;
use Illuminate\Contracts\Validation\ValidationRule;

class FireEventRequest extends FormRequest
{
    use HasLocationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge($this->getLocationRules(), [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                new ShouldNotEndWithFire,
                new DoesntContainEmojis,
            ],
            'frequency' => [
                'required',
                'string',
            ],
        ]);
    }
}
