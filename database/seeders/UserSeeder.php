<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'superadmin@mailinator.com'     => Role::SUPER_ADMIN,
            'admin@mailinator.com'          => Role::ADMIN,
            'pic_executor@mailinator.com'   => Role::PIC_EXECUTOR,
            'pic_project@mailinator.com'    => Role::PIC_PROJECT,
        ];

        foreach ($roles as $email => $roleName) {
            User::factory()->create([
                'email' => $email,
                'role_id' => Role::where('name', $roleName)->value('id'),
            ]);
        }
    }
}
