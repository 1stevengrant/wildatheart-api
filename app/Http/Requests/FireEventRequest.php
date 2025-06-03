<?php

namespace App\Http\Requests;

use App\Rules\ShouldNotEndWithFire;
use Illuminate\Foundation\Http\FormRequest;
use Repat\LaravelRules\DoesntContainEmojis;
use Illuminate\Contracts\Validation\ValidationRule;

class FireEventRequest extends FormRequest
{
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
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                new ShouldNotEndWithFire,
                new DoesntContainEmojis,
            ],
            'country' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'frequency' => [
                'required',
                'string',
            ],
        ];
    }
}
