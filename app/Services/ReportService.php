<?php

namespace App\Services;

use App\Models\Event;
use App\Enums\ReportName;
use App\Enums\ReportFormat;
use App\DataTransferObjects\ReportData;

class ReportService
{
    /**
     * Generates a named report and returns it in the chosen format.
     *
     *
     * @return string|null Returns null if config is invalid, otherwise returns formatted report as string.
     */
    public function generate(ReportName $reportName, ReportFormat $format = ReportFormat::CSV): ?string
    {
        switch ($reportName) {
            case ReportName::BAKR_HOSTS:
                $report = $this->getBAKRHosts();
                break;
            case ReportName::WAHR_HOSTS:
                $report = $this->getWAHRHosts();
                break;
            case ReportName::BAKR_EXPERIENCE_HOSTS:
                $report = $this->getBAKRExperienceHosts();
                break;
            case ReportName::WAHR_EXPERIENCE_HOSTS:
                $report = $this->getWAHExperienceHosts();
                break;
            case ReportName::FIRES_HOSTS:
                $report = $this->getFiresHosts();
                break;
            case ReportName::CAPTIVATING_RETRAT_HOSTS:
                $report = $this->getCAPRHosts();
                break;
            case ReportName::CAPTIVATING_EXPERIENCE_HOSTS:
                $report = $this->getCAPExperienceHosts();
                break;
            case ReportName::SURVIVAL_SCHOOL_HOSTS:
                $report = $this->getSurvivalSchoolHosts();
                break;
        }

        switch ($format) {
            case ReportFormat::CSV:
                return $this->generateCSV($report);
        }

        return null;
    }

    private function getBAKRHosts(): ReportData
    {
        $headers = [
            'given_name' => 'First Name',
            'family_name' => 'Last Name',
            'email' => 'Email',
            'IS_BAKR_HOST' => 'IS_BAKR_HOST',
        ];

        $data = Event::query()
            ->becomingAKingRetreats()
            ->with('user')
            ->get()
            ->unique('user.id')
            ->pluck('user')
            ->map(
                fn($host) => [
                    'given_name' => $host->given_name,
                    'family_name' => $host->family_name,
                    'email' => $host->email,
                    'IS_BAKR_HOST' => 'TRUE',
                ]
            )
            ->toArray();

        return new ReportData($headers, $data);
    }

    private function getBAKRExperienceHosts(): ReportData
    {
        return $this->getHostData(Event::query()->becomingAKingExperiences());
    }

    private function getWAHRHosts(): ReportData
    {
        return $this->getHostData(Event::query()->wahRetreat());
    }

    private function getWAHExperienceHosts(): ReportData
    {
        return $this->getHostData(Event::query()->wahExperience());
    }

    private function getFiresHosts(): ReportData
    {
        return $this->getHostData(Event::query()->fires());
    }

    private function getCAPRHosts(): ReportData
    {
        return $this->getHostData(Event::query()->captivatingRetreat());
    }

    private function getCAPExperienceHosts(): ReportData
    {
        return $this->getHostData(Event::query()->captivatingExperience());
    }

    private function getSurvivalSchoolHosts(): ReportData
    {
        return $this->getHostData(Event::query()->survivalSchool());
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

    /**
     * @param  array<string,string>  $headers  Key-value pairs where keys are data fields and values are header labels
     * @param  array<string,string>  $data  Key-value pairs where keys are data fields and values are corresponding data
     */
    public function generateCSV(ReportData $report)
    {
        $twoMBInBytes = (5 * 1024 * 1024);
        $csv = fopen('php://temp/maxmemory:' . $twoMBInBytes, 'r+');

        fputcsv($csv, array_values($report->headers));

        foreach ($report->data as $item) {
            $headerKeys = array_keys($report->headers);
            fputcsv($csv, array_map(
                fn($key) => data_get($item, $key, ''),
                $headerKeys
            ));
        }

        rewind($csv);

        return stream_get_contents($csv);
    }
}
