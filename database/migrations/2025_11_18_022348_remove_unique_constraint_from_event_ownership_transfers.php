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
        Schema::table('event_ownership_transfers', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['event_id']);
            // Drop unique constraint
            $table->dropUnique('event_ownership_transfers_event_id_unique');
            // Add regular index for performance
            $table->index('event_id');
        });

        // Recreate foreign key in separate schema call
        Schema::table('event_ownership_transfers', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_ownership_transfers', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['event_id']);
            // Drop regular index
            $table->dropIndex(['event_id']);
            // Add unique constraint
            $table->unique('event_id');
        });

        // Recreate foreign key in separate schema call
        Schema::table('event_ownership_transfers', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }
};
