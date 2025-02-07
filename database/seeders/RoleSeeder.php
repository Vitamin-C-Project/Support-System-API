<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = [Role::SUPER_ADMIN, Role::ADMIN, Role::PIC_EXECUTOR, Role::PIC_PROJECT];

        foreach ($role as $roles) {
            Role::create([
                'name' => $roles,
            ]);
        }
    }
}
