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
            [
                'name'     => 'Ayios Andreas',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_AYIOS_ANDREAS',
            ],
            [
                'name'     => 'Ayios Antonios',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_AYIOS_ANTONIOS',
            ],
            [
                'name'     => 'Ayios Dometios',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_AYIOS_DOMETIOS',
            ],
            [
                'name'     => 'Ayioi Konstantinos kai Elenis',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_AYIOI_KON_KAI_ELENIS',
            ],
            [
                'name'     => 'Trypiotis',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_TRYPIOTIS',
            ],
            [
                'name'     => 'Taht-el-kale',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_TAHT_EL_KALE',
            ],
            [
                'name'     => 'Tseri',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_TSERI',
            ],
            [
                'name'     => 'Yeri',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_YERI',
            ],
            [
                'name'     => 'Latsia',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_LATSIA',
            ],
            [
                'name'     => 'Kaimakli',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_KAIMAKLI',
            ],
            [
                'name'     => 'Lakatamia',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_LAKATAMIA',
            ],
             [
                'name'     => 'Yeni Cami',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_YENI_CAMI',
            ],
             [
                'name'     => 'Arap Ahmet',
                'city'     => 'Nicosia',
                'district' => 'Nicosia',
                'geom_ref' => 'NIC_ARAP_AHMET',
            ],
            
        ];

        foreach ($items as $item) {
            DB::table('neighborhoods')->updateOrInsert(
                ['geom_ref' => $item['geom_ref']],
                $item
            );
        }

        // Link each geom_ref to a file
        $geomFiless = Neighborhood::query()->select('id', 'geom_ref')->get();
        foreach ($geomFiless as $item) {
            GeometryHelper::updateSpatialData((object)$item);
        }
    }
}
