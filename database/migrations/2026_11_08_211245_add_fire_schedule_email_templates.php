<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\FireScheduleEmailTemplateSeeder;

return new class extends Migration
{
    public function up(): void
    {
        (new FireScheduleEmailTemplateSeeder)->run();
    }
};
