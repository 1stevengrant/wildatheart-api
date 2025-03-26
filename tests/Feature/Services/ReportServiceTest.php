<?php

use App\Services\ReportService;

beforeEach(function () {
  $this->reportService = new ReportService;
  $this->seed(\Database\Seeders\DatabaseSeeder::class);
  $this->event = \App\Models\Event::factory()->approved()->fires()->create();
});

it(
  'generates CSV',
  function () {

    $data = [
      [
        "first_name" => "Nic",
        "last_name" => "Bovee",
        "age" => 31
      ],
      [
        "first_name" => "Dani",
        "last_name" => "Bovee",
        "age" => 31
      ],
    ];

    $headers = [
      "first_name" => "First Name",
      "last_name" => "Last Name",
      "age" => "Age"
    ];


    $csv = $this->reportService->generateCSV($headers, $data);

    $expectedOutput = "First Name,Last Name,Age\nNic,Bovee,31\nDani,Bovee,31";
    expect($csv)->toBe($expectedOutput);
  }
);
