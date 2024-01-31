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
        Schema::table('events', function (Blueprint $table) {
            $table->after('summary', function ($table) {
                $table->unsignedBigInteger('legacy_drupal_id')->nullable();
                $table->unsignedBigInteger('legacy_drupal_user_id')->nullable();
                $table->json('legacy_drupal_location')->nullable();
                $table->string('legacy_drupal_url')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'legacy_drupal_id',
                'legacy_drupal_user_id',
                'legacy_drupal_location',
                'legacy_drupal_url',
            ]);
        });
    }
};
