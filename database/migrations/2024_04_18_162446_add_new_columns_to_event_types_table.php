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
            $table->after('description', function (Blueprint $table) {
                $table->string('registration_welcome_video')->nullable();
                $table->longText('registration_welcome_text')->nullable();
                $table->longText('registration_before_your_event_text')->nullable();
                $table->json('event_schedule')->nullable();
                $table->json('event_faqs')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn([
                'registration_welcome_video',
                'registration_welcome_text',
                'registration_before_your_event_text',
                'event_schedule',
                'event_faqs',
            ]);
        });
    }
};
