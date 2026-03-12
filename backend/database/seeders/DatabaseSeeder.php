<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // First, run your existing seeders (RoleSeeder, UserSeeder)
            RoleSeeder::class,
            UserSeeder::class,
            
            // Then these master data seeders
            CaseCategorySeeder::class,
            CaseStageSeeder::class,
            CourtSeeder::class,
            DocumentSeeder::class,
        ]);
    }
}