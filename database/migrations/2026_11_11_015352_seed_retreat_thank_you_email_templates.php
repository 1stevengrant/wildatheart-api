<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\RetreatThankYouEmailTemplateSeeder;

return new class extends Migration
{
    public function up(): void
    {
        (new RetreatThankYouEmailTemplateSeeder)->run();
    }
};
