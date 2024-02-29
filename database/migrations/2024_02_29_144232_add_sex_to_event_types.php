<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            // create an enum column of sex with values of men, women or any
            $table->enum('sex', [
                \App\Models\Event::FOR_MEN,
                \App\Models\Event::FOR_WOMEN,
            ])->default(\App\Models\Event::FOR_MEN)
                ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn('sex');
        });
    }
};
