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
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('last_monthly_email_sent_at', 'last_checkin_email_sent_at');
            $table->timestamp('last_checkin_reminder_sent_at')->after('last_checkin_email_sent_at')->nullable();
            $table->timestamp('last_checkin_confirmed_at')->after('last_checkin_reminder_sent_at')->nullable();
            $table->boolean('auto_unpublished')->after('last_checkin_confirmed_at')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('last_checkin_confirmed_at');
            $table->renameColumn('last_checkin_email_sent_at', 'last_monthly_email_sent_at');
            $table->dropColumn('auto_unpublished');
            $table->dropColumn('last_checkin_reminder_sent_at');
        });
    }
};
