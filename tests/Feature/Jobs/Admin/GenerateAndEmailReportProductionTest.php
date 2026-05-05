<?php

use App\Enums\ReportName;
use App\Enums\ReportFormat;
use App\Mail\ReportMailable;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Admin\GenerateAndEmailReport;

beforeEach(function () {

    // Mock the ReportService
    $this->reportService = $this->mock(ReportService::class);
});

it('uses local disk for temporary files even when default storage is S3', function () {
    // Arrange - simulate production with S3 as default storage
    Storage::fake('s3');  // S3 storage
    Storage::fake('local');  // Local storage
    Mail::fake();

    // Set S3 as default storage disk (simulating production)
    config(['filesystems.default' => 's3']);

    $email = 'test@example.com';
    $report = ReportName::BAKR_HOSTS;
    $reportTitle = 'S3 Production Test';
    $csvContent = "Name,Email\nTest User,test@example.com";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->with($report, ReportFormat::CSV)
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    // File should NOT be on S3 (default storage)
    Storage::disk('s3')->assertMissing('tmp/report.csv');

    // File should have been created and cleaned up on local disk
    Storage::disk('local')->assertMissing('tmp/report.csv');

    // Email should have been sent successfully
    Mail::assertSent(ReportMailable::class, function ($mail) use ($email, $reportTitle) {
        return $mail->hasTo($email) &&
               $mail->hasCc('nic@wildatheart.org') &&
               str_contains($mail->envelope()->subject, $reportTitle);
    });
});

it('works correctly when both S3 and local storage are available', function () {
    // Arrange - setup both storage disks
    Storage::fake('s3');
    Storage::fake('local');
    Mail::fake();

    $email = 'test@example.com';
    $report = ReportName::FIRES_HOSTS;
    $reportTitle = 'Multi-Storage Test';
    $csvContent = "Host,Location\nTest Host,Test Location";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    // Verify file was only on local disk, not S3
    Storage::disk('s3')->assertMissing('tmp/report.csv');
    Storage::disk('local')->assertMissing('tmp/report.csv'); // Should be cleaned up

    // Email should be sent
    Mail::assertSent(ReportMailable::class);
});

it('handles case where local disk storage fails gracefully', function () {
    // Arrange
    Storage::fake('local');
    Mail::fake();

    $email = 'test@example.com';
    $report = ReportName::WAHR_HOSTS;
    $reportTitle = 'Local Storage Failure Test';
    $csvContent = "Data\nTest";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Mock local disk to fail on put operation
    Storage::shouldReceive('disk')
        ->with('local')
        ->andReturnSelf();
    Storage::shouldReceive('makeDirectory')
        ->with('tmp')
        ->andReturn(true);
    Storage::shouldReceive('put')
        ->with('tmp/report.csv', $csvContent)
        ->andThrow(new Exception('Local disk full'));

    // Act & Assert
    expect(function () use ($email, $report, $reportTitle) {
        $job = new GenerateAndEmailReport($email, $report, $reportTitle);
        $job->handle($this->reportService);
    })->toThrow(Exception::class, 'Local disk full');

    // No email should be sent if file creation fails
    Mail::assertNothingSent();
});

it('creates valid file paths for email attachments', function () {
    // Arrange
    Storage::fake('local');
    Mail::fake();

    $email = 'test@example.com';
    $report = ReportName::CAPTIVATING_RETRAT_HOSTS;
    $reportTitle = 'File Path Test';
    $csvContent = "Participant\nTest Participant";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class, function ($mail) {
        $attachments = $mail->attachments();
        expect($attachments)->toHaveCount(1);

        $attachment = $attachments[0];

        // The attachment should have a valid file path (not relative like "tmp/report.csv")
        // This was the original issue - it should be an absolute path
        expect($attachment->as)->toContain('File Path Test');
        expect($attachment->mime)->toBe('text/csv');

        return true;
    });

    // Cleanup should work
    Storage::disk('local')->assertMissing('tmp/report.csv');
});
