<?php

namespace Database\Seeders;

use App\Models\Fire;
use Illuminate\Database\Seeder;

class FireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Fire::factory()->count(100)->create();
    }
}
