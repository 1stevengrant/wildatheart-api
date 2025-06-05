<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Models\EventType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
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

        // Check if it's a retreat and adjust end date if needed
        if ($event && $event->eventType->group_type === EventType::TYPE_RETREAT && $this->has('start_date')) {
            $this->merge([
                'end_date' => Carbon::parse($this->start_date)->addDays(3)->format('Y-m-d'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required',
            'longitude' => 'required',
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => 'date|after_or_equal:start_date',
        ];
    }
}
