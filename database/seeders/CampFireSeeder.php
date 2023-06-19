<?php

namespace Database\Seeders;

use App\Models\Campfire;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampFireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Campfire::factory()->private()->count(5)->create([
            'user_id' => User::inRandomOrder()->first()->id,
        ]);
        Campfire::factory()->approved()->count(5)->create([
            'user_id' => User::inRandomOrder()->first()->id,
        ]);
    }
}
