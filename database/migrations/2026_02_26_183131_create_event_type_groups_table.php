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
        Schema::create('event_type_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sort_order')->nullable();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->bigInteger('width')->nullable();
            $table->bigInteger('height')->nullable();
            $table->longText('summary')->nullable();
            $table->timestamps();
        });

        if (Schema::hasColumn('event_types', 'type')) {
            Schema::table('event_types', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        Schema::table('event_types', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_type_groups');
    }
};
