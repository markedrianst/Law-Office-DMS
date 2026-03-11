<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        $courts = [
            [
                'name' => 'RTC Branch 16, Urdaneta City',
                'type' => 'Court',
                'address' => 'Urdaneta City',
                'contact_info' => null,
                'sort_order' => 1
            ],
            [
                'name' => 'MTC Capas, Tarlac',
                'type' => 'Court',
                'address' => 'Capas, Tarlac',
                'contact_info' => null,
                'sort_order' => 2
            ],
            [
                'name' => 'Office of the City Prosecutor – Angeles',
                'type' => 'Prosecutor',
                'address' => 'Angeles City',
                'contact_info' => null,
                'sort_order' => 3
            ],
            [
                'name' => 'DARAB',
                'type' => 'Agency',
                'address' => null,
                'contact_info' => null,
                'sort_order' => 4
            ],
            [
                'name' => 'NLRC',
                'type' => 'Agency',
                'address' => null,
                'contact_info' => null,
                'sort_order' => 5
            ],
            [
                'name' => 'SEC',
                'type' => 'Agency',
                'address' => null,
                'contact_info' => null,
                'sort_order' => 6
            ],
            [
                'name' => 'BIR RDO',
                'type' => 'Agency',
                'address' => null,
                'contact_info' => null,
                'sort_order' => 7
            ],
            [
                'name' => 'Others',
                'type' => 'Others',
                'address' => null,
                'contact_info' => null,
                'sort_order' => 9999
            ],
        ];

        foreach ($courts as $court) {
            DB::table('courts')->insert([
                'name' => $court['name'],
                'type' => $court['type'],
                'address' => $court['address'],
                'contact_info' => $court['contact_info'],
                'is_active' => true,
                'sort_order' => $court['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}