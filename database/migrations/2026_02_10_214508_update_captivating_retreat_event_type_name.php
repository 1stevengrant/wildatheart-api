<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        DB::table('event_types')
            ->where('name', 'Captivating Retreat')
            ->update(['name' => '4-Day Captivating Retreat']);
    }
};
