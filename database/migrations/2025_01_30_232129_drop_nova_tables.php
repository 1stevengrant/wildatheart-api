<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('nova_field_attachments');
        Schema::dropIfExists('nova_notifications');
        Schema::dropIfExists('nova_pending_field_attachments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
