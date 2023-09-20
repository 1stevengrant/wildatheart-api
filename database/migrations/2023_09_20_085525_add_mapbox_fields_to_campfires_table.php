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
        Schema::table('campfires', function (Blueprint $table) {
            $table->after('latitude', function ($table) {
                $table->string('region')->nullable();
                $table->string('district')->nullable();
                $table->string('locality')->nullable();
                $table->string('neighborhood')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campfires', function (Blueprint $table) {
            $table->dropColumn(['region', 'district', 'locality', 'neighborhood']);
        });
    }
};
