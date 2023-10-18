<?php

namespace App\Observers;

use App\Actions\ZendeskCreateTicket;
use App\Jobs\ReportIssueToSlack;
use App\Models\Issue;

class IssueObserver
{
    public function created(Issue $issue): void
    {
        ZendeskCreateTicket::dispatch($issue);
        ReportIssueToSlack::dispatch($issue);
    }
}
