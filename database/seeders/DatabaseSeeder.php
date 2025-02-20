<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\Severity;
use App\Models\Ticket;
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
        Company::factory()->count(10)->create();
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            TicketStatusSeeder::class,
            ProjectSeeder::class
        ]);
        Severity::factory()->count(10)->create();
        Ticket::factory()->count(10)->create();
    }
}
