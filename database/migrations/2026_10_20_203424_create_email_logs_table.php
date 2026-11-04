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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_status');
            $table->string('mailable')->nullable();
            $table->string('subject')->nullable();
            $table->string('from')->nullable();
            $table->text('to')->nullable();
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->longText('html_body')->nullable();
            $table->json('attachments')->nullable();
            $table->string('mailer')->nullable();
            $table->string('message_id')->nullable();
            $table->uuid('event_id')->nullable()->index();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }
};
