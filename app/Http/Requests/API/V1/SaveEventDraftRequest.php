<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Foundation\Http\FormRequest;

class SaveEventDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validValues = array_column(Event::MATERIAL_ORDER_ITEMS, 'value');
        $frequencyValues = array_map(fn (FrequencyEnum $case) => $case->value, FrequencyEnum::cases());

        return [
            'event_id' => ['nullable', 'string', 'uuid'],
            'location' => ['nullable', 'array'],
            'location.address' => ['nullable', 'string'],
            'location.latitude' => ['nullable', 'numeric'],
            'location.longitude' => ['nullable', 'numeric'],
            'name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'frequency' => ['nullable', 'string', 'in:' . implode(',', $frequencyValues)],
            'is_private' => ['nullable', 'boolean'],
            'number_of_items_in_material_order' => ['nullable', 'string', 'in:' . implode(',', $validValues)],
            'co_host_emails' => ['nullable', 'array'],
            'co_host_emails.*' => ['email'],
            'event_info' => ['nullable', 'string', 'max:5000'],
            'expected_attendees' => ['nullable', 'string', 'max:50'],
            'show_address_preference' => ['nullable', 'string', 'in:exact,approximate,hide'],
            'mail_to_preference' => ['nullable', 'string', 'in:mailToEventAddress,mailToDifferentAddress,doNotMailResources'],
            'mail_to_location' => ['nullable', 'array'],
            'mail_to_location.address' => ['nullable', 'string'],
            'mail_to_location.latitude' => ['nullable', 'numeric'],
            'mail_to_location.longitude' => ['nullable', 'numeric'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     * Merge nested location fields into top-level for compatibility with Event model.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->filled('location')) {
            $loc = $this->input('location');
            if (is_array($loc)) {
                $merge['name'] = $merge['name'] ?? $loc['address'] ?? null;
                $merge['latitude'] = $merge['latitude'] ?? $loc['latitude'] ?? null;
                $merge['longitude'] = $merge['longitude'] ?? $loc['longitude'] ?? null;
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }
}
