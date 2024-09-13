<?php

namespace App\Traits\Event;

trait EventScopeTrait
{
    public function scopeWithinBoundingBox(
        $query,
        $latitude,
        $longitude,
        $swLatitude,
        $swLongitude,
        $neLatitude,
        $neLongitude,
        $sex = null,
        $typeGroup = null,
        $types = null,
        $startDate = null,
        $endDate = null
    ) {
        $sex = $sex ?? '';
        $typeGroup = $typeGroup ?? '';
        $endDate = $endDate ?? '';
        $startDate = $startDate ?? now()->format('Y-m-d');

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
            ->whereNotNull('approved_at')
            ->whereNull('deleted_at')
            ->whereBetween('latitude', [$swLatitude, $neLatitude])
            ->whereBetween('longitude', [$swLongitude, $neLongitude])
            ->where('start_date', '>=', $startDate);

        if ($sex) {
            $query->where('sex', $sex);
        }

        if (! empty($types)) {
            $query->whereIn('event_type_id', explode(',', $types));
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
