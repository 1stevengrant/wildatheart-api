<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;

trait HasMaterialOrderValidationAttributes
{
    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $eventType = $this->route('eventType');
        $label = $eventType?->number_of_items_in_material_order_label
            ?? 'expected number of attendees';

        return [
            'number_of_items_in_material_order' => Str::lower($label),
        ];
    }
}
