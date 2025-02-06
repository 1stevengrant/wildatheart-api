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
        Schema::dropIfExists('email_verifies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('email_verifies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token');
            $table->string('event_id');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }
};
