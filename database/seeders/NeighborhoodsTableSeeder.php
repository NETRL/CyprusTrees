<?php

namespace Database\Seeders;

use App\Helpers\GeometryHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NeighborhoodsTableSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Kaimakli',
                'city' => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_KAIMAKLI'
            ],
            [
                'name' => 'Pallouriotissa',
                'city' => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_PALLOURIOTISSA'
            ],
            [
                'name' => 'Ayioi Omoloyites',
                'city' => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_AYIOI_OMOLOYITES'
            ],
        ];

        foreach ($items as $item) {
            DB::table('neighborhoods')->updateOrInsert(
                ['geom_ref' => $item['geom_ref']],
                $item
            );
        }

        // 2. Link each geom_ref to a file
        $geomFiles = [
            ["id" => 1, "geom_ref" => "NIC_KAIMAKLI"],
            ["id" => 2, "geom_ref" => "NIC_PALLOURIOTISSA"],
            ["id" => 3, "geom_ref" => "NIC_AYIOI_OMOLOYITES"],
        ];

        foreach ($geomFiles as $item) {
            GeometryHelper::updateSpatialData($item);
        }
    }
}
