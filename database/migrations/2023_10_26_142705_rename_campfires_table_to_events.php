<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('campfires', 'events');

        Schema::table('addresses', function ($table) {
            $table->renameColumn('campfire_id', 'event_id');
        });

        Schema::table('attendees', function ($table) {
            $table->renameColumn('campfire_id', 'event_id');
        });

        Schema::table('book_shipments', function ($table) {
            $table->renameColumn('campfire_id', 'event_id');
        });

        Schema::table('email_verifies', function ($table) {
            $table->renameColumn('campfire_id', 'event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('events', 'campfires');

        Schema::table('addresses', function ($table) {
            $table->renameColumn('event_id', 'campfire_id');
        });

        Schema::table('attendees', function ($table) {
            $table->renameColumn('event_id', 'campfire_id');
        });

        Schema::table('book_shipments', function ($table) {
            $table->renameColumn('event_id', 'campfire_id');
        });

        Schema::table('email_verifies', function ($table) {
            $table->renameColumn('event_id', 'campfire_id');
        });
    }
};
