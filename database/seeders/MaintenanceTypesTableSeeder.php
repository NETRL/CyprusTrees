<?php

namespace Database\Seeders;

use App\Models\MaintenanceType;
use Illuminate\Database\Seeder;

class MaintenanceTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Prune'],
            ['name' => 'Water'],
            ['name' => 'Pest'],
            ['name' => 'Stake'],
            ['name' => 'Inspect'],
            ['name' => 'Other'],
        ];

        foreach ($items as $item) {
            MaintenanceType::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
