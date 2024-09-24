<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class EventTypeBuilder extends Builder
{
    public function slug(string $slug, string $operator = 'LIKE'): EventTypeBuilder
    {
        return $this->where('slug', $operator, $slug);
    }

    public function activeSlug(string $slug, string $operator = 'LIKE'): EventTypeBuilder
    {
        return $this->slug($slug, $operator)->active();
    }

    public function slugOr(string $slug, string $operator = 'LIKE'): EventTypeBuilder
    {
        return $this->orWhere('slug', $operator, $slug);
    }

    public function active(): EventTypeBuilder
    {
        return $this->where('is_active', true);
    }

    public function inactive(): EventTypeBuilder
    {
        return $this->where('is_active', false);
    }
}
