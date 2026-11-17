<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Services\InternalReportingService;
use App\Http\Requests\Internal\InternalReportingRequest;

class InternalReportingController extends Controller
{
    public function __invoke(InternalReportingRequest $request, InternalReportingService $reporting): Response
    {
        $report = $reporting->forDate($request->reportDate());

        return Inertia::render('Internal/ReportingPage', [
            'date' => $report->date,
            'year' => $report->year,
            'eventStats' => $report->eventStats,
            'yearStats' => $report->yearStats,
        ]);
    }
}
