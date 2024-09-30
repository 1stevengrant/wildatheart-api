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
        Schema::table('event_types', function (Blueprint $table) {
            $table->after('id', function ($table) {
                $table->boolean('allow_materials_order')->default(false);
                $table->string('number_of_items_in_material_order_label')->nullable();
            });
        });

        Schema::table('events', function (Blueprint $table) {
            $table->integer('number_of_items_in_material_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn([
                'allow_materials_order',
                'number_of_items_in_material_order_label'
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('number_of_items_in_material_order');
        });
    }
};
