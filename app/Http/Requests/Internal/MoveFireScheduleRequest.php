<?php

namespace App\Http\Requests\Internal;

use Illuminate\Validation\Rule;
use App\Enums\Event\ScheduleTestPreset;
use Illuminate\Foundation\Http\FormRequest;

class MoveFireScheduleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'preset' => ['required', Rule::enum(ScheduleTestPreset::class)],
        ];
    }

    public function preset(): ScheduleTestPreset
    {
        return ScheduleTestPreset::from($this->validated('preset'));
    }
}
