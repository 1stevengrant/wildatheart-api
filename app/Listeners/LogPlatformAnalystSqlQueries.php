<?php

namespace App\Listeners;

use App\Jobs\LogActivityJob;
use App\Ai\Tools\RunReadOnlySql;
use App\Ai\Agents\PlatformAnalyst;
use Laravel\Ai\Events\ToolInvoked;
use Illuminate\Database\Eloquent\Model;

class LogPlatformAnalystSqlQueries
{
    public function handle(ToolInvoked $event): void
    {
        if (! $event->tool instanceof RunReadOnlySql) {
            return;
        }

        $result = json_decode((string) $event->result, true);

        $causer = $event->agent instanceof PlatformAnalyst
            ? $event->agent->conversationParticipant()
            : null;

        LogActivityJob::dispatch(
            logName: 'platform-analyst',
            description: 'Ran read-only SQL',
            properties: [
                'sql' => $event->arguments['sql'] ?? null,
                'purpose' => $event->arguments['purpose'] ?? null,
                'row_count' => $result['row_count'] ?? null,
                'error' => $result['error'] ?? null,
                'conversation_id' => $event->agent instanceof PlatformAnalyst ? $event->agent->currentConversation() : null,
            ],
            causedBy: $causer instanceof Model ? $causer : null,
        );
    }
}
