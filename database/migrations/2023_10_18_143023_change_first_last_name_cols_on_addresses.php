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
        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('first_name', 'given_name');
            $table->renameColumn('last_name', 'family_name');
            $table->dropColumn(['mapbox_place_name', 'state_code', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('given_name', 'first_name');
            $table->renameColumn('family_name', 'last_name');
            $table->string('mapbox_place_name')->nullable();
            $table->string('state_code')->nullable();
            $table->string('country_code')->nullable();
        });
    }
};
