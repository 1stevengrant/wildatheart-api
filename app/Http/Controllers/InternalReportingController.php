<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class InternalReportingController extends Controller
{
    public function __invoke(Request $request)
    {
        $date = $request->get('date') ?? now()->subDays(30)->startOfDay()->format('Y-m-d');
        $cacheTime = now()->addMinutes(15);
        $year = now()->parse($date)->year;
        $startOfYear = now()->parse($date)->startOfYear();
        $endOfYear = now()->parse($date)->endOfYear();

        return inertia('Internal/ReportingPage', [
            'date' => $date,
            'year' => $year,
            'eventStats' => [
                'fires' => [
                    'count' => cache()->remember('fires.count_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->fires()->count();
                    }),
                    'hosts' => cache()->remember('fires.hosts_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->fires()->distinct('user_id')->count('user_id');
                    }),
                ],
                'wahRetreats' => [
                    'count' => cache()->remember('wah-retreats.count_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->wahRetreat()->count();
                    }),
                    'hosts' => cache()->remember('wah-retreats.hosts_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->wahRetreat()->distinct('user_id')->count('user_id');
                    }),
                ],
                'survivalSchools' => [
                    'count' => cache()->remember('survival.schools.count_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->survivalSchool()->count();
                    }),
                    'hosts' => cache()->remember('survival.schools.hosts_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->survivalSchool()->distinct('user_id')->count('user_id');
                    }),
                ],
                'captivatingRetreats' => [
                    'count' => cache()->remember('captivating-retreats.count_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->captivatingRetreat()->count();
                    }),
                    'hosts' => cache()->remember('captivating-retreats.hosts_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->captivatingRetreat()->distinct('user_id')->count('user_id');
                    }),
                ],
                'captivatingFires' => [
                    'count' => cache()->remember('captivating-fires.count_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->captivatingFire()->count();
                    }),
                    'hosts' => cache()->remember('captivating-fires.hosts_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->captivatingFire()->distinct('user_id')->count('user_id');
                    }),
                ],
                'experiences' => [
                    'count' => cache()->remember('experiences.count_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->experiences()->count();
                    }),
                    'hosts' => cache()->remember('experiences.hosts_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->createdAfter($date)->experiences()->distinct('user_id')->count('user_id');
                    }),
                ],
            ],
            'yearStats' => [
                'fires' => [
                    'count' => cache()->remember('fires.count_year_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->withTrashed()->fires()->whereBetween('updated_at', [
                            now()->parse($date)->startOfYear(),
                            now()->parse($date)->endOfYear(),
                        ])->count();
                    }),
                    'hosts' => cache()->remember('fires.hosts_year_' . $date, $cacheTime, function () use ($date) {
                        return Event::query()->withTrashed()->fires()->whereBetween('updated_at', [
                            now()->parse($date)->startOfYear(),
                            now()->parse($date)->endOfYear(),
                        ])->distinct('user_id')->count('user_id');
                    }),
                ],
                'wahRetreats' => [
                    'count' => cache()->remember('wah-retreats.count_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->wahRetreat()->count();
                    }),
                    'hosts' => cache()->remember('wah-retreats.hosts_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->wahRetreat()->distinct('user_id')->count('user_id');
                    }),
                ],
                'survivalSchools' => [
                    'count' => cache()->remember('survival.schools.count_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->survivalSchool()->count();
                    }),
                    'hosts' => cache()->remember('survival.schools.hosts_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->survivalSchool()->distinct('user_id')->count('user_id');
                    }),
                ],
                'captivatingRetreats' => [
                    'count' => cache()->remember('captivating-retreats.count_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->captivatingRetreat()->count();
                    }),
                    'hosts' => cache()->remember('captivating-retreats.hosts_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->captivatingRetreat()->distinct('user_id')->count('user_id');
                    }),
                ],
                'captivatingFires' => [
                    'count' => cache()->remember('captivating.fires.count_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->captivatingFire()->count();
                    }),
                    'hosts' => cache()->remember('captivating.fires.hosts_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->captivatingFire()->distinct('user_id')->count('user_id');
                    }),
                ],
                'experiences' => [
                    'count' => cache()->remember('experiences.count_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->experiences()->count();
                    }),
                    'hosts' => cache()->remember('experiences.hosts_year_' . $date, $cacheTime, function () use ($startOfYear, $endOfYear) {
                        return Event::query()->withTrashed()->startDateBetween($startOfYear, $endOfYear)->experiences()->distinct('user_id')->count('user_id');
                    }),
                ],
            ],
        ]);
    }
}
