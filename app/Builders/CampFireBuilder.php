<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class CampFireBuilder extends Builder
{
    public function future(): CampFireBuilder
    {
        return $this->where('date', '>=', now())
            ->where('is_private', '=', false)
            ->where('approved_at', '!=', null)
            ->orderBy('date');
    }

    public function futureState(): CampFireBuilder
    {
        return $this->where('date', '>=', now())
            ->where('is_private', '=', false)
            ->where('approved_at', '!=', null)
            ->orderBy('state')
            ->orderBy('date');
    }

    public function private(): CampFireBuilder
    {
        return $this->where('is_private', '=', true)
            ->where('date', '>=', now())
            ->where('approved_at', '!=', null)
            ->orderBy('date');
    }
}
