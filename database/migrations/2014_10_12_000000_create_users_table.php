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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->dateTime('became_host')->nullable();
            $table->dateTime('became_attendee')->nullable();
            $table->dateTime('became_admin')->nullable();
            $table->string('name')->nullable();
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('picture')->nullable();
            $table->string('locale')->nullable();
            $table->boolean('email_verified')->nullable();
            $table->string('iss')->nullable();
            $table->string('aud')->nullable();
            $table->string('iat')->nullable();
            $table->string('exp')->nullable();
            $table->string('sub')->nullable()->unique();
            $table->string('sid')->nullable();
            $table->string('nonce')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->dateTime('last_login_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
