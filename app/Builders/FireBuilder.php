<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class FireBuilder extends Builder
{
    public function future(): FireBuilder
    {
        return $this->where('date', '>=', now());
    }
}
