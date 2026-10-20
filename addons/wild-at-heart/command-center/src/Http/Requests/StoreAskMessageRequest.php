<?php

namespace WildAtHeart\CommandCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAskMessageRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'size:36'],
        ];
    }
}
