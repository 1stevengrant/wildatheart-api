<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class CampFireBuilder extends Builder
{
    public function future(): CampFireBuilder
    {
        return $this->where('date', '>=', now())
            ->where('approved_at', '!=', null);
    }
}
