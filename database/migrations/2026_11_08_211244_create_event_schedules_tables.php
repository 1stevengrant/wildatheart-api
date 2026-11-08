<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('frequency', 20);
            $table->string('timezone', 64);
            $table->time('default_start_time');
            $table->time('default_end_time');
            $table->date('anchor_date')->nullable();
            $table->timestamps();
        });

        Schema::create('event_schedule_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();

            $table->unique(['event_id', 'date']);
            $table->index('ends_at');
        });

        Schema::create('event_schedule_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30);
            $table->date('last_date');
            $table->timestamps();

            $table->unique(['event_id', 'kind', 'last_date']);
        });
    }
};
