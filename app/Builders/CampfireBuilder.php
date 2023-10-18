<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class CampfireBuilder extends Builder
{
    public function future(): CampfireBuilder
    {
        return $this->where('approved_at', '!=', null);
    }

    public function futureState(): CampfireBuilder
    {
        return $this->where('approved_at', '!=', null)
            ->orderBy('state');
    }

    public function public(): CampfireBuilder
    {
        return $this->where('approved_at', '!=', null);
    }
}
