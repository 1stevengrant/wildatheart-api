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
        // Drop and recreate event_manager_invitations with ULID
        Schema::dropIfExists('event_manager_invitations');
        Schema::create('event_manager_invitations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('invited_by_name');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'email']);
        });

        // Drop and recreate event_ownership_transfers with ULID
        Schema::dropIfExists('event_ownership_transfers');
        Schema::create('event_ownership_transfers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('transferred_by_name');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate with integer IDs
        Schema::dropIfExists('event_manager_invitations');
        Schema::create('event_manager_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('invited_by_name');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'email']);
        });

        Schema::dropIfExists('event_ownership_transfers');
        Schema::create('event_ownership_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('transferred_by_name');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique('event_id');
        });
    }
};
