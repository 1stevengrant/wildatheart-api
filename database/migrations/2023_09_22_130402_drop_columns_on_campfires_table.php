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
        Schema::table('campfires', function (Blueprint $table) {
            $table->dropColumn('organization');
            $table->dropColumn('description');
            $table->dropColumn('is_indoor');
            $table->dropColumn('attendee_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campfires', function (Blueprint $table) {
            $table->string('organization')->nullable()->after('name');
            $table->text('description')->nullable()->after('organization');
            $table->boolean('is_indoor')->default(false)->after('mapbox_place_name');
            $table->integer('attendee_limit')->nullable()->after('is_indoor');
        });
    }
};
