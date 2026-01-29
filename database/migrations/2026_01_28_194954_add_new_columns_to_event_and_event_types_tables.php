<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->string('new_image')->after('image')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('new_card_heading')->after('frequency')->nullable();
        });
    }
};
