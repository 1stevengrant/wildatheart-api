<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class CampfireBuilder extends Builder
{
    public function future(): CampfireBuilder
    {
        return $this->where('date', '>=', now())
            ->where('is_private', '=', false)
            ->where('approved_at', '!=', null)
            ->orderBy('date');
    }

    public function futureState(): CampfireBuilder
    {
        return $this->where('date', '>=', now())
            ->where('is_private', '=', false)
            ->where('approved_at', '!=', null)
            ->orderBy('state')
            ->orderBy('date');
    }

    public function private(): CampfireBuilder
    {
        return $this->where('is_private', '=', true)
            ->where('date', '>=', now())
            ->where('approved_at', '!=', null)
            ->orderBy('date');
    }
}
