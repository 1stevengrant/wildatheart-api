<?php

namespace App\Http\Requests\Internal;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class InternalReportingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function reportDate(): CarbonImmutable
    {
        if (! $this->filled('date')) {
            return CarbonImmutable::now()->subDays(30)->startOfDay();
        }

        return CarbonImmutable::createFromFormat('Y-m-d', $this->string('date')->toString())->startOfDay();
    }
}
