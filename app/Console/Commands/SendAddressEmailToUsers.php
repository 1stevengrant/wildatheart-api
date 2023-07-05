<?php

namespace App\Console\Commands;

use App\Mail\Address\PleaseUpdateMailingAddress;
use App\Models\Campfire;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAddressEmailToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-address-email-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email to the campfires where the street address is missing';

    public function handle(): void
    {
        $campfires = Campfire::all()->filter(function ($campfire) {
            return $campfire->hasAddress();
        });

        $campfires = $campfires->filter(function ($campfire) {
            return $campfire->address->street_address === null;
        });

        $campfires->each(function ($campfire) {
            Mail::to($campfire->user)->send(new PleaseUpdateMailingAddress($campfire, $campfire->user));
        });

    }
}
