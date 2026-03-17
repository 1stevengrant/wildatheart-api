<?php

namespace App\Services;

use App\Models\Event;
use App\Enums\ReportName;
use App\Enums\ReportFormat;
use App\DataTransferObjects\ReportData;

class ReportService
{
    private function getScopeForReport(ReportName $reportName): ?string
    {
        return match ($reportName) {
            ReportName::BAKR_HOSTS => 'becomingAKingRetreats',
            ReportName::WAHR_HOSTS => 'wahRetreat',
            ReportName::BAKR_EXPERIENCE_HOSTS => 'becomingAKingExperiences',
            ReportName::WAHR_EXPERIENCE_HOSTS => 'wahExperience',
            ReportName::FIRES_HOSTS => 'fires',
            ReportName::CAPTIVATING_RETRAT_HOSTS => 'captivatingRetreat',
            ReportName::CAPTIVATING_EXPERIENCE_HOSTS => 'captivatingExperience',
            ReportName::SURVIVAL_SCHOOL_HOSTS => 'survivalSchool',
        };
    }

    /**
     * Generates a named report and returns it in the chosen format.
     *
     * @return string|null Returns null if config is invalid, otherwise returns formatted report as string.
     */
    public function generate(ReportName $reportName, ReportFormat $format = ReportFormat::CSV): ?string
    {
        $scope = $this->getScopeForReport($reportName);

        $report = $this->getHostData(Event::query()->{$scope}());

        return match ($format) {
            ReportFormat::CSV => $this->generateCSV($report),
            default => null,
        };
    }

    private function getHostData($query): ReportData
    {
        $headers = [
            'given_name' => 'First Name',
            'family_name' => 'Last Name',
            'email' => 'Email',
            'mailing_address' => 'Mailing Address',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip',
            'country' => 'Country',
        ];

        $data = $query
            ->with(['user', 'user.addresses' => function ($query) {
                $query->where('is_primary', true);
            }])
            ->get()
            ->unique('user.id')
            ->pluck('user')
            ->map(function ($user) {
                return [
                    'given_name' => $user->given_name,
                    'family_name' => $user->family_name,
                    'email' => $user->email,
                    'mailing_address' => $user->addresses->first()?->full_address ?? '',
                    'city' => $user->addresses->first()?->city ?? '',
                    'state' => $user->addresses->first()?->state ?? '',
                    'zip' => $user->addresses->first()?->zip ?? '',
                    'country' => $user->addresses->first()?->country ?? '',
                ];
            })->toArray();

        return new ReportData($headers, $data);
    }

    public function generateCSV(ReportData $report): string
    {
        $fiveMBInBytes = (5 * 1024 * 1024);
        $csv = fopen('php://temp/maxmemory:' . $fiveMBInBytes, 'r+');

        fputcsv($csv, array_values($report->headers));

        foreach ($report->data as $item) {
            $headerKeys = array_keys($report->headers);
            fputcsv($csv, array_map(
                fn ($key) => data_get($item, $key, ''),
                $headerKeys
            ));
        }

        rewind($csv);

        return stream_get_contents($csv);
    }
}
