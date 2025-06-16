<?php

namespace App\Observers;

use App\Models\Issue;
use App\Actions\ZendeskCreateTicket;
use App\Jobs\Assets\ReportIssueToSlack;

class IssueObserver
{
    public function created(Issue $issue): void
    {
        ZendeskCreateTicket::dispatch($issue);
        ReportIssueToSlack::dispatch($issue);
    }
}
