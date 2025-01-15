<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class InternalReportingController extends Controller
{
    public function __invoke(Request $request)
    {
        $date = $this->getReportDate($request);
        $cacheTime = now()->addMinutes(15);
        $year = now()->parse($date)->year;
        $startOfYear = now()->parse($date)->startOfYear();
        $endOfYear = now()->parse($date)->endOfYear();

        $eventTypes = [
            'fires' => 'fires',
            'wahRetreats' => 'wahRetreat',
            'survivalSchools' => 'survivalSchool',
            'captivatingRetreats' => 'captivatingRetreat',
            'captivatingFires' => 'captivatingFire',
            'experiences' => 'experiences',
        ];

        $eventStats = $this->getEventStats($eventTypes, $date, $cacheTime);
        $yearStats = $this->getYearStats($eventTypes, $startOfYear, $endOfYear, $cacheTime);

        return inertia('Internal/ReportingPage', [
            'date' => $date,
            'year' => $year,
            'eventStats' => $eventStats,
            'yearStats' => $yearStats,
        ]);
    }

    private function getReportDate(Request $request): string
    {
        return $request->get('date') ?? now()->subDays(30)->startOfDay()->format('Y-m-d');
    }

    private function getEventStats(array $eventTypes, string $date, $cacheTime): array
    {
        return array_map(function ($scope) use ($date, $cacheTime) {
            return [
                'count' => cache()->remember("{$scope}.count_{$date}", $cacheTime, function () use ($scope, $date) {
                    return Event::query()->createdAfter($date)->$scope()->count();
                }),
                'hosts' => cache()->remember("{$scope}.hosts_{$date}", $cacheTime, function () use ($scope, $date) {
                    return Event::query()->createdAfter($date)->$scope()->distinct('user_id')->count('user_id');
                }),
            ];
        }, $eventTypes);
    }

    private function getYearStats(array $eventTypes, $startOfYear, $endOfYear, $cacheTime): array
    {
        return array_map(function ($scope) use ($startOfYear, $endOfYear, $cacheTime) {
            return [
                'count' => cache()->remember("{$scope}.count_year", $cacheTime, function () use ($scope, $startOfYear, $endOfYear) {
                    return Event::query()
                        ->withTrashed()
                        ->startDateBetween($startOfYear, $endOfYear)
                        ->$scope()
                        ->count();
                }),
                'hosts' => cache()->remember("{$scope}.hosts_year", $cacheTime, function () use ($scope, $startOfYear, $endOfYear) {
                    return Event::query()
                        ->withTrashed()
                        ->startDateBetween($startOfYear, $endOfYear)
                        ->$scope()
                        ->distinct('user_id')
                        ->count('user_id');
                }),
            ];
        }, $eventTypes);
    }
}
