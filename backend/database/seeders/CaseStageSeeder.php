<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Intake / Client Interview', 'color' => '#3b82f6', 'order' => 1],
            ['name' => 'For Document Preparation', 'color' => '#8b5cf6', 'order' => 2],
            ['name' => 'For Lawyer Review', 'color' => '#f59e0b', 'order' => 3],
            ['name' => 'For Filing', 'color' => '#10b981', 'order' => 4],
            ['name' => 'Filed / Pending', 'color' => '#6b7280', 'order' => 5],
            ['name' => 'Hearing / Proceedings', 'color' => '#ef4444', 'order' => 6],
            ['name' => 'For Decision / Resolution', 'color' => '#8b5cf6', 'order' => 7],
            ['name' => 'Closed', 'color' => '#64748b', 'order' => 8],
        ];

        foreach ($stages as $stage) {
            DB::table('case_stages')->insert([
                'name' => $stage['name'],
                'color' => $stage['color'],
                'order' => $stage['order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}