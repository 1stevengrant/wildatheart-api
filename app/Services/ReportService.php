<?php

namespace App\Services;

use App\DataTransferObjects\ReportData;
use App\Models\User;
use App\Models\EventType;
use App\Models\Event;

enum ReportFormat: string
{
  case CSV = 'csv';
}

enum ReportName: string
{
  case BAKR_HOSTS = 'getBAKRHosts';
}

class ReportService
{
  /**
   * Generates a named report and returns it in the chosen format.
   * 
   * @param ReportName $reportName 
   * @param ReportFormat $format
   * 
   * @return string|null Returns null if config is invalid, otherwise returns formatted report as string.
   */
  public function generate(ReportName $reportName, ReportFormat $format = ReportFormat::CSV): string | null
  {
    switch ($reportName) {
      case ReportName::BAKR_HOSTS:
        $report = $this->getBAKRHosts();
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
      "given_name" => "First Name",
      "family_name" => "Last Name",
      "email" => "Email",
      "IS_BAKR_HOST" => "IS_BAKR_HOST"
    ];

    $data =  Event::query()
      ->becomingAKingRetreats()
      ->with("user")
      ->get()
      ->unique("user.id")
      ->pluck("user")
      ->map(
        fn($host) => [
          "given_name" => $host->given_name,
          "family_name" => $host->family_name,
          "email" => $host->email,
          "IS_BAKR_HOST" => "TRUE"
        ]
      )
      ->toArray();

    return new ReportData($headers, $data);
  }

  /**
   * @param array<string,string> $headers Key-value pairs where keys are data fields and values are header labels
   * @param array<string,string> $data Key-value pairs where keys are data fields and values are corresponding data
   */
  public function generateCSV(ReportData $report)
  {
    $twoMBInBytes = (5 * 1024 * 1024);
    $csv = fopen('php://temp/maxmemory:' . $twoMBInBytes, 'r+');

    fputcsv($csv, array_values($report->headers));

    foreach ($report->data as $item) {
      $headerKeys = array_keys($report->headers);
      fputcsv($csv, array_map(
        fn($key) => data_get($item, $key, ""),
        $headerKeys
      ));
    }

    rewind($csv);
    return stream_get_contents($csv);
  }
}
