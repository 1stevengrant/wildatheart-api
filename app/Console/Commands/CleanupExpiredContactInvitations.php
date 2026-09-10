<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\ContactInvitation\CleanupExpiredContactInvitations as CleanupAction;

class CleanupExpiredContactInvitations extends Command
{
    protected $signature = 'contact-invitations:cleanup';

    protected $description = 'Remove staged images and deactivate expired contact invitations';

    public function handle(): int
    {
        $count = CleanupAction::run();
        $this->comment("Cleaned up {$count} expired contact invitation(s).");

        return self::SUCCESS;
    }
}
