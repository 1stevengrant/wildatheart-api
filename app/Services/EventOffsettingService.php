<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class EventOffsettingService
{
    public function __construct(
        private readonly float $baseOffsetRadius = 0.005,
        private readonly int $maxMarkersPerRing = 8,
        private readonly float $spreadFactor = 1.5,
        private readonly float $minOffset = 0.001
    ) {}

    public function offsetEvents(Collection $events): Collection
    {
        $groups = $this->groupEvents($events);

        foreach ($groups as $group) {
            $this->offsetGroup($group);
        }

        return $events;
    }

    private function groupEvents(Collection $events): array
    {
        $groups = [];
        foreach ($events as $event) {
            $key = round($event->latitude, 3).','.round($event->longitude, 3);
            $groups[$key][] = $event;
        }

        return $groups;
    }

    private function offsetGroup(array $group): void
    {
        $baseEvent = $group[0];
        $groupSize = count($group);

        $offsetRadius = max(
            $this->baseOffsetRadius * (1 + log10(max($groupSize, 1))) * $this->spreadFactor,
            $this->minOffset
        );

        foreach ($group as $index => $event) {
            if ($index == 0 && $groupSize == 1) {
                // For single events, apply a small random offset
                $randomAngle = mt_rand(0, 360) * (M_PI / 180);
                $latOffset = $this->minOffset * sin($randomAngle);
                $lngOffset = $this->minOffset * cos($randomAngle) / cos(deg2rad($baseEvent->latitude));
            } elseif ($index == 0) {
                // Slightly offset the first event in a group
                $latOffset = $offsetRadius * 0.1;
                $lngOffset = $offsetRadius * 0.1;
            } else {
                $angle = (2 * M_PI / ($groupSize - 1)) * ($index - 1);
                $ring = floor(($index - 1) / $this->maxMarkersPerRing);
                $radius = $offsetRadius * ($ring + 1);

                $latOffset = $radius * sin($angle);
                $lngOffset = $radius * cos($angle) / cos(deg2rad($baseEvent->latitude));
            }

            $event->display_latitude = round($baseEvent->latitude + $latOffset, 2);
            $event->display_longitude = round($baseEvent->longitude + $lngOffset, 2);
            $event->group_size = $groupSize;
        }
    }
}
