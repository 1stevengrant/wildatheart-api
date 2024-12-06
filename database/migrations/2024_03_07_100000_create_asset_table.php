<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Statamic\Eloquent\Database\BaseMigration as Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix('assets_meta'), function (Blueprint $table) {
            $table->id();
            $table->string('container')->index();
            $table->string('folder')->index();
            $table->string('basename')->index();
            $table->string('filename')->index();
            $table->char('extension', 10)->index();
            $table->string('path')->index();
            $table->jsonb('meta')->nullable();
            $table->timestamps();

            $table->unique(['container', 'folder', 'basename']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix('assets_meta'));
    }
};
