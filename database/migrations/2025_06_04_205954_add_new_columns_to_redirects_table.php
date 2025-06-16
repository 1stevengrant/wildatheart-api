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
        Schema::table('redirects', function (Blueprint $table) {
            $table->string('status_code')->nullable();
            $table->unsignedBigInteger('count')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('system_origin')->nullable();
            $table->unsignedBigInteger('last_accessed_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redirects', function (Blueprint $table) {
            $table->dropColumn(['status_code', 'count', 'created_by', 'system_origin', 'last_accessed_on']);
        });
    }
};
