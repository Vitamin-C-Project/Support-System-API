<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersId = User::get()->pluck('id');
        $companyId = Company::get()->pluck('id');

        if ($usersId->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            Project::factory()->create([
                'user_id' => $usersId->random(),
                'company_id' => $companyId->random(),
            ]);
        }
    }
}
