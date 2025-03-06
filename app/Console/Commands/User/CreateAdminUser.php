<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $familyName = $this->ask('What is your family name?');
        $givenName = $this->ask('What is your given name?');
        $email = $this->ask('What is your email address?');
        $password = $this->secret('What is the password?');

        if (User::where('email', $email)->exists()) {
            $this->error('User already exists.');
            User::where('email', $email)->first()->assignRole(User::ROLE_ADMIN);
            $this->info('User role updated successfully.');

            return;
        }

        $user = User::create([
            'family_name' => $familyName,
            'given_name' => $givenName,
            'email' => $email,
            'password' => bcrypt($password),
            'name' => $givenName . ' ' . $familyName,
        ]);

        $user->assignRole(User::ROLE_ADMIN);

        $this->info('Admin user created successfully.');
    }
}
