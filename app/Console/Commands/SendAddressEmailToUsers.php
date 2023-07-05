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
        $fires = Campfire::all()->filter(function ($fire) {
            return $fire->hasAddress();
        });

        $fires = $fires->filter(function ($fire) {
            return $fire->address->street_address === null;
        });

        $fires->each(function ($fire) {
            Mail::to($fire->user)->send(new PleaseUpdateMailingAddress($fire, $fire->user));
        });

    }
}
