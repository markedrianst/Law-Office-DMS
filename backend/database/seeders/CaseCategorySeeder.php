<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Criminal', 'color' => '#dc2626', 'sort_order' => 1],
            ['name' => 'Annulment', 'color' => '#9333ea', 'sort_order' => 2],
            ['name' => 'Civil', 'color' => '#2563eb', 'sort_order' => 3],
            ['name' => 'Land Issues', 'color' => '#d97706', 'sort_order' => 4],
            ['name' => 'Land Transfer', 'color' => '#ea580c', 'sort_order' => 5],
            ['name' => 'Pending', 'color' => '#64748b', 'sort_order' => 6],
            ['name' => 'Admin', 'color' => '#4f46e5', 'sort_order' => 7],
            ['name' => 'Other', 'color' => '#6b7280', 'sort_order' => 9999],
        ];

        foreach ($categories as $cat) {
            DB::table('case_categories')->insert([
                'name' => $cat['name'],
                'color' => $cat['color'],
                'sort_order' => $cat['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}