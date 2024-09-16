<?php

namespace App\Traits\Event;

trait EventScopeTrait
{
    public function scopeWithinDistance(
        $query,
        $latitude,
        $longitude,
        $radius = 10,
        $sex = null,
        $typeGroup = null,
        $types = null,
        $startDate = null,
        $endDate = null
    ) {
        $sex = $sex ?? '';
        $typeGroup = $typeGroup ?? '';
        $startDate = $startDate ?? '';
        $endDate = $endDate ?? '';

        $query
            ->select(
                'id',
                'name',
                'latitude',
                'longitude',
                'country',
                'sex',
                'event_type_id',
                'start_date',
                'end_date',
                'user_id',
                'country',
                'region',
                'city',
                'district',
                'state'
            )
            ->selectRaw(
                '(3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->whereNotNull('approved_at')
            ->whereNull('deleted_at')
            ->having('distance', '<=', $radius)
            ->orderBy('distance');

        if ($sex) {
            $query->where('sex', $sex);
        }

        if (! empty($types)) {
            $query->whereIn('event_type_id', explode(',', $types));
        }

        if ($startDate) {
            $query->whereRaw('(start_date >= ? OR start_date IS NULL)', [$startDate]);
        } else {
            $query->whereRaw('(start_date >= CURDATE() OR start_date IS NULL)');
        }

        if ($endDate) {
            $query->whereRaw('(end_date <= ? OR end_date IS NULL)', [$endDate]);
        }

        if ($typeGroup) {
            $query->whereHas('eventType', function ($query) use ($typeGroup) {
                $query->where('name', 'like', '%'.$typeGroup.'%');
            });
        }

        return $query;
    }
}
