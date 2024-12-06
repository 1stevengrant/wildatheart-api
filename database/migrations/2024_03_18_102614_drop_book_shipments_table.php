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
        Schema::dropIfExists('book_shipments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('book_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn');
            $table->string('condition');
            $table->string('status');
            $table->timestamps();
        });
    }
};
