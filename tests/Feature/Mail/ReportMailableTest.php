<?php

use App\Mail\ReportMailable;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

it('creates mailable with correct envelope data', function () {
    // Arrange
    $date = '01/15/2024';
    $filePath = Storage::path('test-report.csv');
    $reportName = 'Test Report';

    // Create a test file
    Storage::put('test-report.csv', 'Name,Email\nJohn,john@example.com');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $envelope = $mailable->envelope();

    // Assert
    expect($envelope->subject)->toBe('Test Report Report - 01/15/2024');
});

it('creates mailable with default report name', function () {
    // Arrange
    $date = '12/25/2023';
    $filePath = Storage::path('test-report.csv');

    // Create a test file
    Storage::put('test-report.csv', 'Data\nTest');

    // Act
    $mailable = new ReportMailable($date, $filePath);
    $envelope = $mailable->envelope();

    // Assert
    expect($envelope->subject)->toBe(' Report - 12/25/2023');
});

it('uses correct markdown template for content', function () {
    // Arrange
    $date = '06/30/2024';
    $filePath = Storage::path('test-report.csv');
    $reportName = 'Monthly Report';

    // Create a test file
    Storage::put('test-report.csv', 'Month,Count\nJune,100');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $content = $mailable->content();

    // Assert
    expect($content->markdown)->toBe('emails.report');
    expect($content->with['date'])->toBe($date);
    expect($content->with['reportName'])->toBe($reportName);
});

it('creates attachment with correct filename and mime type', function () {
    // Arrange
    $date = '03/15/2024';
    $filePath = Storage::path('test-report.csv');
    $reportName = 'Quarterly Data';

    // Create a test file
    Storage::put('test-report.csv', 'Quarter,Revenue\nQ1,50000');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $attachments = $mailable->attachments();

    // Assert
    expect($attachments)->toHaveCount(1);

    $attachment = $attachments[0];
    expect($attachment->mime)->toBe('text/csv');

    // Check filename format: "Report Name MM-DD-YYYY_report.csv"
    $expectedFileName = 'Quarterly Data ' . now()->format('m-d-Y_') . 'report.csv';
    expect($attachment->as)->toBe($expectedFileName);
});

it('generates filename with current date when creating attachment', function () {
    // Arrange
    $date = '01/01/2024';
    $filePath = Storage::path('test-report.csv');
    $reportName = 'New Year Report';

    // Create a test file
    Storage::put('test-report.csv', 'Event,Attendees\nNew Year Party,250');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $attachments = $mailable->attachments();

    // Assert
    $attachment = $attachments[0];
    $currentDate = now()->format('m-d-Y_');
    expect($attachment->as)->toContain($currentDate);
    expect($attachment->as)->toContain('New Year Report');
    expect($attachment->as)->toEndWith('report.csv');
});

it('handles empty report name in attachment filename', function () {
    // Arrange
    $date = '07/04/2024';
    $filePath = Storage::path('test-report.csv');
    $reportName = '';

    // Create a test file
    Storage::put('test-report.csv', 'Holiday,Date\nIndependence Day,07/04/2024');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $attachments = $mailable->attachments();

    // Assert
    $attachment = $attachments[0];
    $expectedFileName = ' ' . now()->format('m-d-Y_') . 'report.csv';
    expect($attachment->as)->toBe($expectedFileName);
});

it('attaches file from correct path', function () {
    // Arrange
    $date = '09/15/2024';
    $reportName = 'September Report';
    $csvContent = 'Product,Sales\nWidget A,1000\nWidget B,750';

    // Create test file
    Storage::put('reports/september.csv', $csvContent);
    $filePath = Storage::path('reports/september.csv');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $attachments = $mailable->attachments();

    // Assert
    expect($attachments)->toHaveCount(1);

    $attachment = $attachments[0];
    expect($attachment->as)->toBe('September Report ' . now()->format('m-d-Y_') . 'report.csv');
    expect($attachment->mime)->toBe('text/csv');
});

it('passes correct data to markdown template', function () {
    // Arrange
    $date = '11/30/2024';
    $filePath = Storage::path('monthly-report.csv');
    $reportName = 'November Monthly Report';

    // Create test file
    Storage::put('monthly-report.csv', 'Metric,Value\nTotal Users,5000');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $content = $mailable->content();

    // Assert
    expect($content->with)->toHaveKey('date');
    expect($content->with)->toHaveKey('reportName');
    expect($content->with['date'])->toBe($date);
    expect($content->with['reportName'])->toBe($reportName);
});

it('handles special characters in report name for filename', function () {
    // Arrange
    $date = '12/01/2024';
    $filePath = Storage::path('test-report.csv');
    $reportName = 'Users & Events Report (Q4)';

    // Create test file
    Storage::put('test-report.csv', 'Type,Count\nUsers,1500\nEvents,300');

    // Act
    $mailable = new ReportMailable($date, $filePath, $reportName);
    $attachments = $mailable->attachments();

    // Assert
    $attachment = $attachments[0];
    $expectedFileName = 'Users & Events Report (Q4) ' . now()->format('m-d-Y_') . 'report.csv';
    expect($attachment->as)->toBe($expectedFileName);
});
