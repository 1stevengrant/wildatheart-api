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
        Schema::create('search_indexes', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->timestamp('built_at');
        });

        Schema::create('search_documents', function (Blueprint $table) {
            $table->id();
            $table->string('index');
            $table->string('reference');
            $table->json('fields');
            $table->longText('content');
            $table->timestamps();

            $table->unique(['index', 'reference']);
        });
    }
};
