<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            // Pleading (Blue theme)
            ['type' => 'Complaint', 'color' => '#2563eb', 'category' => 'Pleading', 'requires_approval' => true, 'sort_order' => 1],
            ['type' => 'Answer', 'color' => '#3b82f6', 'category' => 'Pleading', 'requires_approval' => true, 'sort_order' => 2],
            ['type' => 'Motion', 'color' => '#4f46e5', 'category' => 'Pleading', 'requires_approval' => true, 'sort_order' => 3],
            ['type' => 'Affidavit', 'color' => '#6366f1', 'category' => 'Pleading', 'requires_approval' => true, 'sort_order' => 4],
            
            // Letter (Green theme)
            ['type' => 'Demand Letter', 'color' => '#16a34a', 'category' => 'Letter', 'requires_approval' => true, 'sort_order' => 5],
            ['type' => 'SPA', 'color' => '#22c55e', 'category' => 'Letter', 'requires_approval' => true, 'sort_order' => 6],
            ['type' => 'Notice', 'color' => '#86efac', 'category' => 'Letter', 'requires_approval' => false, 'sort_order' => 7],
            
            // Court Issuance (Red theme)
            ['type' => 'Subpoena', 'color' => '#b91c1c', 'category' => 'Court Issuance', 'requires_approval' => false, 'sort_order' => 8],
            ['type' => 'Court Order', 'color' => '#dc2626', 'category' => 'Court Issuance', 'requires_approval' => false, 'sort_order' => 9],
            
            // Evidence (Amber theme)
            ['type' => 'Proof of Service', 'color' => '#b45309', 'category' => 'Evidence', 'requires_approval' => false, 'sort_order' => 10],
            ['type' => 'Registry Receipt', 'color' => '#d97706', 'category' => 'Evidence', 'requires_approval' => false, 'sort_order' => 11],
            ['type' => 'ID\'s', 'color' => '#f59e0b', 'category' => 'Evidence', 'requires_approval' => false, 'sort_order' => 12],
            ['type' => 'TCT Title', 'color' => '#fbbf24', 'category' => 'Evidence', 'requires_approval' => false, 'sort_order' => 13],
            ['type' => 'Receipts', 'color' => '#fcd34d', 'category' => 'Evidence', 'requires_approval' => false, 'sort_order' => 14],
            
            // Other (Gray)
            ['type' => 'Others', 'color' => '#6b7280', 'category' => 'Other', 'requires_approval' => false, 'sort_order' => 9999],
        ];

        foreach ($documents as $doc) {
            DB::table('documents')->insert([
                'type' => $doc['type'],
                'color' => $doc['color'],
                'category' => $doc['category'],
                'requires_approval' => $doc['requires_approval'],
                'is_active' => true,
                'sort_order' => $doc['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}