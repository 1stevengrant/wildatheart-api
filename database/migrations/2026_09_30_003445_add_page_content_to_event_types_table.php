<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->string('page_logo')->nullable();
            $table->string('page_background_video_url', 1024)->nullable();
            $table->string('page_background_image')->nullable();
            $table->json('page_topics')->nullable();
            $table->string('page_leader_text')->nullable();
            $table->json('page_hero_title')->nullable();
            $table->text('page_hero_subtitle')->nullable();
            $table->string('page_featured_video_url', 1024)->nullable();
            $table->string('page_featured_video_fallback_image')->nullable();
            $table->longText('page_description')->nullable();
        });
    }
};
