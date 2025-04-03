<?php

use App\Services\ReportService;
use App\DataTransferObjects\ReportData;

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
                'first_name' => 'Nic',
                'last_name' => 'Bovee',
                'age' => 31,
            ],
            [
                'first_name' => 'Dani',
                'last_name' => 'Bovee',
                'age' => 31,
            ],
        ];

        $headers = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'age' => 'Age',
        ];

        $reportData = new ReportData($headers, $data);
        $csv = $this->reportService->generateCSV($reportData);

        $expectedOutput = "\"First Name\",\"Last Name\",Age\nNic,Bovee,31\nDani,Bovee,31\n";
        expect($csv)->toBe($expectedOutput);
    }
);

it(
    'escapes special characters in CSV',
    function () {
        $data = [
            [
                'first_name' => 'John, with comma',
                'last_name' => 'Smith "quotes"',
                'notes' => "Has \n newline",
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'notes' => 'Normal text',
            ],
        ];

        $headers = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'notes' => 'Notes',
        ];

        $reportData = new ReportData($headers, $data);
        $csv = $this->reportService->generateCSV($reportData);

        $expectedOutput = "\"First Name\",\"Last Name\",Notes\n\"John, with comma\",\"Smith \"\"quotes\"\"\",\"Has \n newline\"\nJane,Doe,\"Normal text\"\n";
        expect($csv)->toBe($expectedOutput);
    }
);
