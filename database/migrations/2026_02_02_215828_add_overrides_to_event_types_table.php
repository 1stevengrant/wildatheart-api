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
            $table->after('video_link', function ($table) {
                $table->string('welcome_nav_text_override')->nullable();
                $table->string('before_event_nav_text_override')->nullable();
                $table->string('schedule_nav_text_override')->nullable();
                $table->string('faq_nav_text_override')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn(['welcome_nav_text_override', 'before_event_nav_text_override', 'schedule_nav_text_override', 'faq_nav_text_override']);
        });
    }
};
