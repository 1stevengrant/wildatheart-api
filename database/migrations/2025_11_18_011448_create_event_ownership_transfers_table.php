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
        Schema::create('event_ownership_transfers', function (Blueprint $table) {
            $table->id();
            $table->char('event_id', 36);
            $table->string('email');
            $table->string('transferred_by_name');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('cascade');

            // Only one pending transfer per event at a time
            $table->unique(['event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_ownership_transfers');
    }
};
