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
        Schema::create('contact_invitations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('invited_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('existing_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('given_name', 100);
            $table->string('family_name', 100);
            $table->string('phone', 30)->nullable();
            $table->string('staged_image_path')->nullable();
            $table->char('token_hash', 64)->unique();
            $table->char('active_key', 64)->nullable()->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['email', 'expires_at']);
        });
    }
};
