<?php

use Illuminate\Support\Facades\DB;
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
            // Add new boolean columns
            $table->boolean('is_featured')->default(false)->after('featured_on');
            $table->boolean('always_show')->default(false)->after('is_featured');
        });

        // Migrate existing featured_on data to is_featured
        DB::table('events')
            ->whereNotNull('featured_on')
            ->update(['is_featured' => true]);

        // Drop the old featured_on column
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('featured_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Add back the featured_on column
            $table->datetime('featured_on')->nullable()->after('custom_landing_url');
        });

        // Migrate is_featured back to featured_on with current timestamp
        DB::table('events')
            ->where('is_featured', true)
            ->update(['featured_on' => now()]);

        // Drop the new columns
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'always_show']);
        });
    }
};
