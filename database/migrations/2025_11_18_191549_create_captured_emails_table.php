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
        Schema::create('captured_emails', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // Recipients
            $table->json('to');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();

            // Sender information
            $table->string('from');
            $table->string('reply_to')->nullable();

            // Email content
            $table->string('subject');
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();

            // Metadata
            $table->json('headers')->nullable();
            $table->json('attachments')->nullable();
            $table->string('mailable_class')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index('created_at');
            $table->index('mailable_class');
        });
    }
};
