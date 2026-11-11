<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = file_get_contents(database_path('seeders/email_templates.sql'));
        \DB::unprepared($sql);

        $this->call(RetreatThankYouEmailTemplateSeeder::class);

        EmailTemplate::all()->each(function (EmailTemplate $emailTemplate) {
            $emailTemplate->update([
                'last_updated_by' => User::first()->id,
            ]);
        });
    }
}
