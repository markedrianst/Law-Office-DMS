<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = Role::pluck('id', 'name')->toArray();

        $users = [
            [
                'role_id' => $roles['admin'],
                'full_name' => 'Admin User',
                'email' => 'admin@lawoffice.com',
                'password_hash' => Hash::make('password123'),
                'status' => 'active',
                'must_change_password' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => $roles['clerk'],
                'full_name' => 'Clerk User',
                'email' => 'clerk@lawoffice.com',
                'password_hash' => Hash::make('password123'),
                'status' => 'active',
                'must_change_password' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => $roles['lawyer'],
                'full_name' => 'Lawyer User',
                'email' => 'lawyer@lawoffice.com',
                'password_hash' => Hash::make('password123'),
                'status' => 'active',
                'must_change_password' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => $roles['clerk'],
                'full_name' => 'John Doe',
                'email' => 'john@lawoffice.com',
                'password_hash' => Hash::make('password123'),
                'status' => 'active',
                'must_change_password' => true, // Force password change
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => $roles['lawyer'],
                'full_name' => 'Jane Smith',
                'email' => 'jane@lawoffice.com',
                'password_hash' => Hash::make('password123'),
                'status' => 'inactive', // Inactive account
                'must_change_password' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        User::insert($users);
    }
}