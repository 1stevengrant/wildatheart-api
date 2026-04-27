<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->string('type')->nullable()->after('group_type')->index();
        });

        DB::table('event_types')->where('group_type', 'fire')->update(['type' => 'gathering']);
        DB::table('event_types')->where('group_type', 'retreat')->update(['type' => 'retreat']);
        DB::table('event_types')->where('group_type', 'experience')->update(['type' => 'course']);
    }
};
