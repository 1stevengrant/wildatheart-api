<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use Illuminate\Validation\Validator;
use App\Support\EventPublishReadiness;
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
     * Publishing from the dashboard sends no fields, so check the saved event itself.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $event = $this->route('event');

                if (! $event instanceof Event) {
                    return;
                }

                foreach (EventPublishReadiness::errors($event) as $field => $message) {
                    $validator->errors()->add($field, $message);
                }
            },
        ];
    }
}
