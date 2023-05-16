<?php

namespace Database\Seeders;

use App\Models\CampFire;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampFireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CampFire::factory()->private()->count(5)->create([
            'user_id' => User::inRandomOrder()->first()->id,
        ]);
        CampFire::factory()->approved()->count(5)->create([
            'user_id' => User::inRandomOrder()->first()->id,
        ]);
    }
}
