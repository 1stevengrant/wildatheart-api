<?php

namespace App\Services;

use App\Models\User;
use App\Models\EventType;
use App\Models\Event;

class ReportService
{
  public function generate()
  {

    $hosts = $this->getBAKRHosts();
    return $hosts;



    // // Create/open the file
    // $handle = fopen(storage_path("app/bak_hosts.csv"), "w");
    // // Write headers
    // fputcsv($handle, ["ID", "Family Name", "Given Name", "Email", "Address"]);
    // // Write data rows
    // foreach ($csvData as $row) {
    //   fputcsv($handle, [
    //     $row["id"],
    //     $row["family_name"],
    //     $row["given_name"],
    //     $row["email"],
    //     implode(", ", $row["address"])
    //   ]);
    // }
    // fclose($handle);
    // echo "CSV saved to: " . storage_path("app/bak_hosts.csv");
  }

  private function getBAKRHosts()
  {
    return Event::query()
      ->becomingAKingRetreats()
      ->with("user")
      ->get()
      ->unique("user.id")
      ->pluck("user")
      ->map(
        fn($host) => [
          "given_name" => $host->given_name,
          "family_name" => $host->family_name,
          "email" => $host->email
        ]
      );
    // ->map(
    //   fn($event) => [
    //     "host" => $event->host,
    //     "event" => $event
    //   ]
    // )
    // ->unique("user.id")
    // ->values();
  }

  /**
   * @param array<string,string> $headers Key-value pairs where keys are data fields and values are header labels
   * @param array<string,string> $data Key-value pairs where keys are data fields and values are corresponding data
   */
  public function generateCSV(array $headers, array $data)
  {
    $csv = join(",", $headers);

    foreach ($data as $itemIndex => $item) {
      $csv .= "\n";
      $headerKeys = array_keys($headers);
      $csv .= join(",", array_map(fn($key) => data_get($item, $key, ""), $headerKeys));
    }
    return $csv;
  }
}
