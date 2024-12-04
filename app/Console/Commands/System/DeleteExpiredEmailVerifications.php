<?php

namespace App\Console\Commands\System;

use App\Models\EmailVerify;
use Illuminate\Console\Command;

class DeleteExpiredEmailVerifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-email-verifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete email verification records that have expired';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Deleting expired email verification records...');

        $count = EmailVerify::where('expires_at', '<', now())->delete();

        $this->info("Deleted {$count} expired email verification records.");
    }
}
