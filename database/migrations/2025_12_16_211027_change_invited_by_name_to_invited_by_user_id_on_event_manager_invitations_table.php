<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Add nullable column first
        Schema::table('event_manager_invitations', function (Blueprint $table) {
            $table->unsignedBigInteger('invited_by_user_id')->nullable()->after('email');
        });

        // Migrate existing data - set invited_by_user_id to the event owner
        DB::statement('
            UPDATE event_manager_invitations
            JOIN events ON event_manager_invitations.event_id = events.id
            SET event_manager_invitations.invited_by_user_id = events.user_id
        ');

        // Make column required and add foreign key
        Schema::table('event_manager_invitations', function (Blueprint $table) {
            $table->unsignedBigInteger('invited_by_user_id')->nullable(false)->change();
            $table->foreign('invited_by_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropColumn('invited_by_name');
        });
    }
};
