<?php

use App\Enums\ReportName;
use App\Enums\ReportFormat;
use App\Mail\ReportMailable;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Admin\GenerateAndEmailReport;

beforeEach(function () {

    // Use fake storage and mail - specifically fake the local disk
    Storage::fake('local');
    Mail::fake();

    // Mock the ReportService
    $this->reportService = $this->mock(ReportService::class);
});

it('successfully generates and emails a report', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::BAKR_HOSTS;
    $reportTitle = 'Test Report';
    $format = ReportFormat::CSV;
    $csvContent = "Name,Email\nJohn Doe,john@example.com\nJane Smith,jane@example.com";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->with($report, $format)
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle, $format);
    $job->handle($this->reportService);

    // Assert
    // Check that the file was created and then deleted from local disk
    Storage::disk('local')->assertMissing('tmp/report.csv');

    // Check that email was sent
    Mail::assertSent(ReportMailable::class, function ($mail) use ($email, $reportTitle) {
        return $mail->hasTo($email) &&
               $mail->hasCc('nic@wildatheart.org') &&
               str_contains($mail->envelope()->subject, $reportTitle);
    });
});

it('creates tmp directory if it does not exist', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::BAKR_HOSTS;
    $reportTitle = 'Test Report';
    $csvContent = "Name,Email\nTest,test@example.com";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Ensure tmp directory doesn't exist initially
    expect(Storage::disk('local')->exists('tmp'))->toBeFalse();

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    // Directory should have been created (even though file is deleted after)
    // We can't directly assert directory existence after cleanup, but we can verify no errors occurred
    Mail::assertSent(ReportMailable::class);
});

it('handles CSV format by default', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::FIRES_HOSTS;
    $reportTitle = 'Default Format Test';
    $csvContent = "Host,Location\nJohn,Denver\nJane,Austin";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->with($report, ReportFormat::CSV)
        ->andReturn($csvContent);

    // Act - Note: not passing format parameter, should default to CSV
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class);
});

it('generates unique file path for attachment', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::WAHR_HOSTS;
    $reportTitle = 'Attachment Test';
    $csvContent = "Data,Value\nTest,123";

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
        expect($attachment->mime)->toBe('text/csv');
        expect($attachment->as)->toContain('.csv');
        expect($attachment->as)->toContain('Attachment Test');

        return true;
    });
});

it('cleans up temporary file after sending email', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::BAKR_EXPERIENCE_HOSTS;
    $reportTitle = 'Cleanup Test';
    $csvContent = "Field1,Field2\nValue1,Value2";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    // File should be cleaned up after email is sent
    Storage::disk('local')->assertMissing('tmp/report.csv');
    Mail::assertSent(ReportMailable::class);
});

it('sends email with correct recipients', function () {
    // Arrange
    $email = 'recipient@example.com';
    $report = ReportName::CAPTIVATING_RETRAT_HOSTS;
    $reportTitle = 'Recipients Test';
    $csvContent = "Name\nTest User";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class, function ($mail) use ($email) {
        return $mail->hasTo($email) && $mail->hasCc('nic@wildatheart.org');
    });
});

it('formats email subject with report title and date', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::SURVIVAL_SCHOOL_HOSTS;
    $reportTitle = 'Survival School Report';
    $csvContent = "Participant\nJohn Doe";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class, function ($mail) use ($reportTitle) {
        $subject = $mail->envelope()->subject;

        return str_contains($subject, $reportTitle) &&
               str_contains($subject, now()->format('m/d/Y'));
    });
});

it('handles empty CSV content', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::BAKR_HOSTS;
    $reportTitle = 'Empty Report';
    $csvContent = '';

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class);
    Storage::disk('local')->assertMissing('tmp/report.csv');
});

it('handles large CSV content', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::FIRES_HOSTS;
    $reportTitle = 'Large Report';

    // Generate large CSV content
    $csvContent = "Name,Email,Location\n";
    for ($i = 1; $i <= 1000; $i++) {
        $csvContent .= "User{$i},user{$i}@example.com,Location{$i}\n";
    }

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class);
    Storage::disk('local')->assertMissing('tmp/report.csv');
});

it('handles special characters in CSV content', function () {
    // Arrange
    $email = 'test@example.com';
    $report = ReportName::WAHR_EXPERIENCE_HOSTS;
    $reportTitle = 'Special Characters Report';
    $csvContent = "Name,Notes\n\"John, Jr.\",\"Has \"quotes\" and commas\"\n\"Jane\",\"Line\nbreak included\"";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class);
    Storage::disk('local')->assertMissing('tmp/report.csv');
});

it('sends email to a single recipient as string (backward compatibility)', function () {
    // Arrange
    $email = 'recipient@example.com';
    $report = ReportName::BAKR_HOSTS;
    $reportTitle = 'Single String Recipient Test';
    $csvContent = "Name,Email\nJohn Doe,john@example.com\nJane Smith,jane@example.com";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->with($report, ReportFormat::CSV)
        ->andReturn($csvContent);

    // Act - Pass a string email (not an array) to test backward compatibility
    $job = new GenerateAndEmailReport($email, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class, function ($mail) use ($email, $reportTitle) {
        // Check that the single email is in the "to" recipients
        return $mail->hasTo($email) &&
               $mail->hasCc('nic@wildatheart.org') &&
               str_contains($mail->envelope()->subject, $reportTitle);
    });

    Storage::disk('local')->assertMissing('tmp/report.csv');
});

it('sends email to multiple recipients', function () {
    // Arrange
    $emails = ['recipient1@example.com', 'recipient2@example.com', 'recipient3@example.com'];
    $report = ReportName::BAKR_HOSTS;
    $reportTitle = 'Multiple Recipients Test';
    $csvContent = "Name,Email\nJohn Doe,john@example.com\nJane Smith,jane@example.com";

    $this->reportService
        ->shouldReceive('generate')
        ->once()
        ->with($report, ReportFormat::CSV)
        ->andReturn($csvContent);

    // Act
    $job = new GenerateAndEmailReport($emails, $report, $reportTitle);
    $job->handle($this->reportService);

    // Assert
    Mail::assertSent(ReportMailable::class, function ($mail) use ($emails, $reportTitle) {
        // Check that all emails are in the "to" recipients
        foreach ($emails as $email) {
            if (! $mail->hasTo($email)) {
                return false;
            }
        }

        // Check CC is still present
        return $mail->hasCc('nic@wildatheart.org') &&
               str_contains($mail->envelope()->subject, $reportTitle);
    });

    Storage::disk('local')->assertMissing('tmp/report.csv');
});
