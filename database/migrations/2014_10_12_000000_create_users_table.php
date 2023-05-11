<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('picture')->nullable();
            $table->string('locale')->nullable();
            $table->boolean('email_verified')->nullable();
            $table->string('iss')->nullable();
            $table->string('aud')->nullable()->unique()->index();
            $table->string('iat')->nullable();
            $table->string('exp')->nullable();
            $table->string('sub')->nullable();
            $table->string('sid')->nullable();
            $table->string('nonce')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
