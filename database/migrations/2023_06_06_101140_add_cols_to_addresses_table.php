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
        Schema::table('addresses', function (Blueprint $table) {
            $table->after('mapbox_place_name', function ($table) {
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('street_address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('state_code')->nullable();
                $table->string('zip')->nullable();
                $table->string('country_code')->nullable();
                $table->string('country')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('is_primary')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'street_address',
                'city',
                'state',
                'state_code',
                'zip',
                'country_code',
                'country',
                'phone',
                'is_primary',
            ]);
        });
    }
};
