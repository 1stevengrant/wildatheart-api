<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Statamic\Eloquent\Database\BaseMigration as Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix('taxonomy_terms'), function (Blueprint $table) {
            $table->id();
            $table->string('site')->index();
            $table->string('slug');
            $table->string('uri')->nullable()->index();
            $table->string('taxonomy')->index();
            $table->jsonb('data');
            $table->timestamps();

            $table->unique(['slug', 'taxonomy', 'site']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix('taxonomy_terms'));
    }
};
