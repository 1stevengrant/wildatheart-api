<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ExportHostContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'array'],
            'filter.search' => ['nullable', 'string', 'max:100'],
            'filter.event' => ['nullable', 'string'],
            'filter.archived' => ['nullable', Rule::in(['false', 'true', 'all'])],
            'filter.added_from' => ['nullable', Rule::date()->format('Y-m-d')],
            'filter.added_to' => ['nullable', Rule::date()->format('Y-m-d')],
            'contacts' => ['nullable', 'array', 'max:1000'],
            'contacts.*.event_id' => ['required', 'uuid'],
            'contacts.*.user_id' => ['required', 'integer'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return $this->validated('filter') ?? [];
    }

    /** @return array<int, array{event_id: string, user_id: int}> */
    public function selectedContacts(): array
    {
        return $this->validated('contacts') ?? [];
    }
}
