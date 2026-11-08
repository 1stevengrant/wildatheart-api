<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;
use App\Support\Schedule\ScheduleReadiness;
use Illuminate\Foundation\Http\FormRequest;

class ToggleEventApprovedAtRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->event->isOwnedOrManagedBy($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->event->approved_at !== null) {
                    return;
                }

                if (! ScheduleReadiness::canPublish($this->event)) {
                    $validator->errors()->add('schedule', ScheduleReadiness::MISSING_DATE_MESSAGE);
                }
            },
        ];
    }
}
