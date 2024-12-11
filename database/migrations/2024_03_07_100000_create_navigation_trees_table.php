<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Statamic\Eloquent\Database\BaseMigration as Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix('trees'), function (Blueprint $table) {
            $table->id();
            $table->string('handle');
            $table->string('type')->index();
            $table->string('locale')->nullable()->index();
            $table->jsonb('tree')->nullable();
            $table->jsonb('settings')->nullable();
            $table->timestamps();

            $table->unique(['handle', 'type', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix('trees'));
    }
};
