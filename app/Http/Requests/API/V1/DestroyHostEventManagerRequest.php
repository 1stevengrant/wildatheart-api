<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class DestroyHostEventManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && $this->user()->id === $event->user_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
