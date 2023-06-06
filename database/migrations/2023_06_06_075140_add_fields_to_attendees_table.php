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
        Schema::table('attendees', function (Blueprint $table) {
            $table->after('user_id', function ($table) {
                $table->string('given_name')->nullable();
                $table->string('family_name')->nullable();
                $table->string('email')->nullable();
                $table->string('country_code')->nullable();
                $table->string('phone')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->boolean('marketing_updates')->default(true);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropColumn('given_name');
            $table->dropColumn('family_name');
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('email_updates');
        });
    }
};
