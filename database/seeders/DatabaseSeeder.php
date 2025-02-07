<?php

namespace Database\Seeders;

use App\Models\Severity;
use App\Models\User;
use Database\Factories\SeverityFackerFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        Severity::factory()->count(10)->create();
    }
}
