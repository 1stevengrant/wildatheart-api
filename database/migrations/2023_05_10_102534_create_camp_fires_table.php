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
        Schema::create('camp_fires', function (Blueprint $table) {
            $table->id();
            $table->longText('description');
            $table->string('phone');
            $table->string('address');
            $table->string('state');
            $table->string('city');
            $table->string('zip');
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->date('date');
            $table->time('time');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('attendee_limit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camp_fires');
    }
};
