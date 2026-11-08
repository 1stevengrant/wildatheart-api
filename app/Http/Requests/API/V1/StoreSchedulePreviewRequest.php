<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Validation\Rule;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreSchedulePreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'frequency' => ['required', 'string', Rule::in([FrequencyEnum::WEEKLY->value, FrequencyEnum::MONTHLY->value])],
            'anchor_date' => ['required', 'date_format:Y-m-d'],
            'count' => ['nullable', 'integer', 'min:1', 'max:24'],
        ];
    }
}
