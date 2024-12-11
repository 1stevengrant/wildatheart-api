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
            $table->dropColumn(['mapbox_place_name', 'book_request_count', 'approved_by', 'is_existing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campfires', function (Blueprint $table) {
            $table->string('mapbox_place_name')->nullable();
            $table->integer('book_request_count')->default(0);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->boolean('is_existing')->default(false);
        });
    }
};
