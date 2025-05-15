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
        Schema::create('ip_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('ip')->unique();
            $table->string('hostname')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('org')->nullable();
            $table->string('postal')->nullable();
            $table->string('timezone')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_lookup_at');
            $table->timestamps();

            $table->index('last_lookup_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_lookups');
    }
};
