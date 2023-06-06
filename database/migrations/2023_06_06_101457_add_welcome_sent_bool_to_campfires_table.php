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
            $table->boolean('welcome_sent')->default(false)->after('approved_at');

            $table->dropColumn('has_different_mailing_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campfires', function (Blueprint $table) {
            $table->dropColumn('welcome_sent');
        });
    }
};
