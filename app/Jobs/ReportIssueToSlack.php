<?php

namespace App\Jobs;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\SlackAlerts\Facades\SlackAlert;

class ReportIssueToSlack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Issue $issue) {}

    public function handle(): void
    {
        if (app()->isProduction()) {

            $issue = $this->issue;

            SlackAlert::blocks([
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "We have feedback from:\n*{$issue->name}*",
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Subject:*\n {$issue->subject}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Date:*\n {$issue->created_at}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Description:*\n {$issue->description}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Email:*\n {$issue->email}",
                        ],
                    ],
                ],
            ]);
        }

    }
}
