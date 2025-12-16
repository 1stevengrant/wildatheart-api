<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_ownership_transfers', function (Blueprint $table) {
            $table->foreignId('transferred_by_user_id')->after('email')->constrained('users')->cascadeOnDelete();
            $table->dropColumn('transferred_by_name');
        });
    }
};
