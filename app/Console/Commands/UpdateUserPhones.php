<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateUserPhones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-user-phones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::all();

        $countryCodeToPrefix = [
            '64' => '+64',
            '61' => '+61',
            '593' => '+593',
            '56' => '+56',
            '55' => '+55',
            '49' => '+49',
            '48' => '+48',
            '47' => '+47',
            '45' => '+45',
            '44' => '+44',
            '43' => '+43',
            '42' => '+42',
            '41' => '+41',
            '39' => '+39',
            '31' => '+31',
            '30' => '+30',
            '27' => '+27',
            '256' => '+256',
            '254' => '+254',
            '20' => '+20',
            '1' => '+1',
        ];

        $users->each(function ($user) use ($countryCodeToPrefix) {
            foreach ($countryCodeToPrefix as $code => $prefix) {
                if (str_contains($user->country_code, $code)) {
                    $user->phone = $prefix.$user->phone;
                    break;
                }
            }
            $user->save();
        });
    }
}
