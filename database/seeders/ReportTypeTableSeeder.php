<?php

namespace Database\Seeders;

use App\Models\ReportType;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ReportTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Damage'],
            ['name' => 'Irrigation'],
            ['name' => 'Disease'],
            ['name' => 'Suggestion'],
        ];

        foreach ($items as $item) {
            ReportType::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
