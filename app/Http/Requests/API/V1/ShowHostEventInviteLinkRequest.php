<?php

namespace App\Http\Requests\API\V1;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class ShowHostEventInviteLinkRequest extends FormRequest
{
    /**
     * The link lets anyone join as a host, so only the event's owner can get it.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && $event->user_id === $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
