<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Models\EventType;
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

        // Check if it's a retreat and adjust end date if needed
        if ($event && $event->eventType->group_type === EventType::TYPE_RETREAT && $this->has('start_date')) {
            $this->merge([
                'end_date' => Carbon::parse($this->start_date)->addDays(3)->format('Y-m-d'),
            ]);
        }
    }

    public function rules(): array
    {
        return array_merge($this->getLocationRules(), [
            'start_date' => ['required', 'date'],
        ]);
    }
}
