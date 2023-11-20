<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class EventBuilder extends Builder
{
    public function future(): EventBuilder
    {
        return $this->where('approved_at', '!=', null);
    }

    public function futureState(): EventBuilder
    {
        return $this->where('approved_at', '!=', null)
            ->orderBy('state');
    }

    public function public(): EventBuilder
    {
        return $this->where('approved_at', '!=', null);
    }
}
