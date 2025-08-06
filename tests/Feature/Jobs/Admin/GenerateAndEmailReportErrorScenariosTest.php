<?php

use App\Enums\ReportName;
use App\Enums\ReportFormat;
use App\Mail\ReportMailable;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Admin\GenerateAndEmailReport;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // Use fake storage and mail - specifically fake the local disk
    Storage::fake('local');
    Mail::fake();

    // Mock the ReportService
    $this->reportService = $this->mock(ReportService::class);
});

it('handles ReportService exception gracefully', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::BAKR_HOSTS;
    $reportTitle = 'Error Test Report';

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->with($report, ReportFormat::CSV)
        ->andThrow(new \Exception('Database connection failed'));

    // Act & Assert
    expect(function () use ($email, $report, $reportTitle) {
        $job = new GenerateAndEmailReport($email, $report, $reportTitle);
        $job->handle($this->reportService);
    })->toThrow(\Exception::class, 'Database connection failed');

    // Verify no email was sent and no file remnants
    Mail::assertNothingSent();
    Storage::disk('local')->assertMissing('tmp/report.csv');
});

it('handles email sending with invalid addresses', function () {
    // Arrange
    $email = 'not-a-valid-email-format';
    $report = ReportName::FIRES_HOSTS;
    $reportTitle = 'Invalid Email Test';
    $csvContent = "Name,Email\nTest,test@example.com";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act - Laravel should handle invalid email gracefully
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert - File should still be cleaned up
    Storage::disk('local')->assertMissing('tmp/report.csv');
    // Note: Laravel's Mail fake doesn't validate email format, so this test
    // verifies the job completes without throwing exceptions
});

it('handles very large CSV content that might cause memory issues', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::FIRES_HOSTS;
    $reportTitle = 'Large Memory Test';

    // Create a moderately large string (simulate large report)
    $largeCsvContent = str_repeat("User{n},email{n}@example.com,Location{n}\n", 1000);

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($largeCsvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class);
    Storage::disk('local')->assertMissing('tmp/report.csv');
});

it('handles empty or null CSV content from ReportService', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::BAKR_EXPERIENCE_HOSTS;
    $reportTitle = 'Empty Content Test';

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn(null);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert - Should handle null gracefully
    Mail::assertSent(ReportMailable::class);
    Storage::disk('local')->assertMissing('tmp/report.csv');
});

it('tests different report types work correctly', function () {
    // Arrange
    $email = 'test@example.com';
    $reportTitle = 'Multi Report Test';
    $csvContent = "Name,Type\nTest,Sample";

    $reports = [
        ReportName::BAKR_HOSTS,
        ReportName::WAHR_HOSTS,
        ReportName::FIRES_HOSTS,
        ReportName::CAPTIVATING_RETRAT_HOSTS,
        ReportName::SURVIVAL_SCHOOL_HOSTS,
    ];

    foreach ($reports as $report) {
        $this->reportService
            ->shouldReceive('generate')
            ->once()
            ->with($report, ReportFormat::CSV)
            ->andReturn($csvContent);

        // Act
        $job = new GenerateAndEmailReport($email, $report, $reportTitle);
        $job->handle($this->reportService);

        // Assert
        Storage::disk('local')->assertMissing('tmp/report.csv');
    }

    // All reports should have been sent
    Mail::assertSent(ReportMailable::class, count($reports));
});
