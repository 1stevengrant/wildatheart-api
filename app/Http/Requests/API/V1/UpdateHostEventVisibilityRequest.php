<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHostEventVisibilityRequest extends FormRequest
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
        return [
            'is_private' => ['required', 'boolean'],
        ];
    }
}
