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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('optimized_image');
            $table->dropColumn('uuid');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid()->after('id')->unique()->nullable();
            $table->string('optimized_image')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('optimized_image');
            $table->dropColumn('uuid');
        });
    }
};
