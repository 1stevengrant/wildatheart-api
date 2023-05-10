<?php

namespace Database\Seeders;

use App\Models\CampFire;
use Illuminate\Database\Seeder;

class CampFireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CampFire::factory()->count(100)->create();
    }
}
