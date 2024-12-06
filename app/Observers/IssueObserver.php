<?php

namespace App\Observers;

use App\Models\Issue;
use App\Jobs\ReportIssueToSlack;
use App\Actions\ZendeskCreateTicket;

class IssueObserver
{
    public function created(Issue $issue): void
    {
        ZendeskCreateTicket::dispatch($issue);
        ReportIssueToSlack::dispatch($issue);
    }
}
