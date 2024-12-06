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
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->string('image')->nullable()->after('description');
            $table->string('hero_image')->nullable()->after('image');
            $table->string('hero_video')->nullable()->after('hero_image');
            $table->json('photos')->nullable()->after('hero_video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->string('icon')->nullable()->after('description');
            $table->dropColumn('hero_image');
            $table->dropColumn('hero_video');
            $table->dropColumn('photos');
        });
    }
};
