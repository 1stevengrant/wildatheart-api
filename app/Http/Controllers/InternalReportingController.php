<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class InternalReportingController extends Controller
{
    private const EVENT_TYPES = [
        'fires' => 'fires',
        'wahRetreats' => 'wahRetreat',
        'survivalSchools' => 'survivalSchool',
        'captivatingRetreats' => 'captivatingRetreat',
        'captivatingFires' => 'captivatingFire',
        'experiences' => 'experiences',
    ];

    public function __invoke(Request $request)
    {
        $date = $request->get('date') ?? now()->subDays(30)->startOfDay()->format('Y-m-d');
        $cacheTime = now()->addMinutes(15);
        $year = now()->parse($date)->year;
        $startOfYear = now()->parse($date)->startOfYear();
        $endOfYear = now()->parse($date)->endOfYear();

        $eventStats = [];
        $yearStats = [];

        foreach (self::EVENT_TYPES as $key => $scope) {
            $eventStats[$key] = $this->getEventStats($scope, $key, $date, $cacheTime);
            $yearStats[$key] = $this->getYearStats($scope, $key, $date, $startOfYear, $endOfYear, $cacheTime);
        }

        return inertia('Internal/ReportingPage', [
            'date' => $date,
            'year' => $year,
            'eventStats' => $eventStats,
            'yearStats' => $yearStats,
        ]);
    }

    /**
     * @return array{count: int, hosts: int}
     */
    private function getEventStats(string $scope, string $cacheKey, string $date, \DateTimeInterface $cacheTime): array
    {
        return [
            'count' => cache()->remember("{$cacheKey}.count_{$date}", $cacheTime, function () use ($scope, $date) {
                return Event::query()->createdAfter($date)->{$scope}()->count();
            }),
            'hosts' => cache()->remember("{$cacheKey}.hosts_{$date}", $cacheTime, function () use ($scope, $date) {
                return Event::query()->createdAfter($date)->{$scope}()->distinct('user_id')->count('user_id');
            }),
        ];
    }

    /**
     * @return array{count: int, hosts: int}
     */
    private function getYearStats(string $scope, string $cacheKey, string $date, $startOfYear, $endOfYear, \DateTimeInterface $cacheTime): array
    {
        $isFireScope = in_array($scope, ['fires', 'captivatingFire']);

        return [
            'count' => cache()->remember("{$cacheKey}.count_year_{$date}", $cacheTime, function () use ($scope, $date, $startOfYear, $endOfYear, $isFireScope) {
                $query = Event::query()->withTrashed()->{$scope}();

                return $isFireScope
                    ? $query->whereBetween('updated_at', [now()->parse($date)->startOfYear(), now()->parse($date)->endOfYear()])->count()
                    : $query->startDateBetween($startOfYear, $endOfYear)->count();
            }),
            'hosts' => cache()->remember("{$cacheKey}.hosts_year_{$date}", $cacheTime, function () use ($scope, $date, $startOfYear, $endOfYear, $isFireScope) {
                $query = Event::query()->withTrashed()->{$scope}();

                return $isFireScope
                    ? $query->whereBetween('updated_at', [now()->parse($date)->startOfYear(), now()->parse($date)->endOfYear()])->distinct('user_id')->count('user_id')
                    : $query->startDateBetween($startOfYear, $endOfYear)->distinct('user_id')->count('user_id');
            }),
        ];
    }
}
