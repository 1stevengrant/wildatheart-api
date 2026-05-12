<?php

namespace App\Http\Requests;

use App\Support\EventDateHelper;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasLocationRules;

class UpdateEventRequest extends FormRequest
{
    use HasLocationRules;

    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Get the event
        $event = $this->route('event');

        if ($event && $this->has('start_date')) {
            EventDateHelper::adjustEndDateForRetreat(
                request: $this,
                groupType: $event->eventType->group_type,
                eventTypeName: $event->eventType->name,
            );
        }
    }

    public function rules(): array
    {
        return array_merge($this->getLocationRules(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);
    }
}
