<?php

namespace App\Builders;

use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class EventTypeBuilder extends Builder
{
    public function slug(string $slug, string $operator = 'LIKE'): EventTypeBuilder
    {
        return $this->where('slug', $operator, $slug);
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

    /**
     * Adds listings_count and countries_count for approved, public events
     * that are upcoming or undated.
     */
    public function withPublicListingCounts(): EventTypeBuilder
    {
        return $this
            ->withCount([
                'events as listings_count' => function (Builder $query): void {
                    $query->whereNotNull('approved_at')
                        ->where('is_private', false)
                        ->where(function (Builder $query): void {
                            $query->where('start_date', '>=', now())
                                ->orWhereNull('start_date');
                        });
                },
            ])
            ->addSelect([
                'countries_count' => Event::query()
                    ->select(DB::raw('COUNT(DISTINCT country)'))
                    ->whereColumn('event_type_id', 'event_types.id')
                    ->whereNotNull('country')
                    ->whereNotNull('approved_at')
                    ->where('is_private', false)
                    ->where(function (Builder $query): void {
                        $query->where('start_date', '>=', now()->toDateString())
                            ->orWhereNull('start_date');
                    }),
            ]);
    }
}
