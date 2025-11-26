<?php

namespace Database\Seeders;

use App\Helpers\GeometryHelper;
use App\Models\Neighborhood;
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
            [
                'name'     => 'Engomi',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_ENGOMI',
            ],
            [
                'name'     => 'Strovolos',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_STROVOLOS',
            ],
            [
                'name'     => 'Aglandjia',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_AGLANDJIA',
            ],
            [
                'name'     => 'Within the Walls',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_WITHIN_WALLS',
            ],
        ];

        foreach ($items as $item) {
            DB::table('neighborhoods')->updateOrInsert(
                ['geom_ref' => $item['geom_ref']],
                $item
            );
        }

        // Link each geom_ref to a file
        $geomFiless = Neighborhood::query()->select('id','geom_ref')->get();
        foreach ($geomFiless as $item) {
            GeometryHelper::updateSpatialData((object)$item);
        }
    }
}
