<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use Illuminate\Validation\Validator;
use App\Support\Schedule\ScheduleReadiness;
use Illuminate\Foundation\Http\FormRequest;

class ApproveHostEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && $event->isOwnedOrManagedBy($this->user());
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
                if (! ScheduleReadiness::canPublish($this->route('event'))) {
                    $validator->errors()->add('schedule', ScheduleReadiness::MISSING_DATE_MESSAGE);
                }
            },
        ];
    }
}
