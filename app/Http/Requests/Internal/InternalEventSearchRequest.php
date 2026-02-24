<?php

namespace App\Http\Requests\Internal;

use Illuminate\Validation\Rule;
use App\Enums\Internal\EventSearchField;
use Illuminate\Foundation\Http\FormRequest;

class InternalEventSearchRequest extends FormRequest
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
        $fieldValues = array_column(EventSearchField::cases(), 'value');

        return [
            'lat' => ['required_with:lng', 'nullable', 'numeric', 'between:-90,90'],
            'lng' => ['required_with:lat', 'nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'event_type_ids' => ['nullable', 'array'],
            'event_type_ids.*' => ['integer', 'exists:event_types,id'],
            'fields' => ['nullable', 'array'],
            'fields.*' => ['string', Rule::in($fieldValues)],
            'place_name' => ['nullable', 'string', 'max:255'],
            'active_only' => ['nullable', 'in:0,1,true,false'],
            'include_deleted' => ['nullable', 'in:0,1,true,false'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lat.required_with' => 'Latitude is required when longitude is provided.',
            'lat.between' => 'Latitude must be between -90 and 90 degrees.',
            'lng.required_with' => 'Longitude is required when latitude is provided.',
            'lng.between' => 'Longitude must be between -180 and 180 degrees.',
            'radius.min' => 'Radius must be at least 1 mile.',
            'radius.max' => 'Radius cannot exceed 500 miles.',
            'event_type_ids.*.exists' => 'One or more selected event types are invalid.',
            'fields.*.in' => 'One or more selected fields are invalid.',
        ];
    }

    public function hasSearchCoordinates(): bool
    {
        return $this->filled('lat') && $this->filled('lng');
    }

    public function lat(): float
    {
        return $this->float('lat');
    }

    public function lng(): float
    {
        return $this->float('lng');
    }

    public function radius(): float
    {
        return $this->float('radius', 30);
    }

    /**
     * @return array<int, int>
     */
    public function eventTypeIds(): array
    {
        return $this->input('event_type_ids', []);
    }

    /**
     * @return array<int, string>
     */
    public function fields(): array
    {
        return $this->input('fields', EventSearchField::defaults());
    }

    public function placeName(): string
    {
        return $this->string('place_name', '')->toString();
    }

    public function activeOnly(): bool
    {
        return $this->boolean('active_only', true);
    }

    public function includeDeleted(): bool
    {
        return $this->boolean('include_deleted', false);
    }

    public function createdFrom(): ?string
    {
        return $this->filled('created_from')
            ? $this->date('created_from')->format('Y-m-d')
            : null;
    }

    public function createdTo(): ?string
    {
        return $this->filled('created_to')
            ? $this->date('created_to')->format('Y-m-d')
            : null;
    }
}
