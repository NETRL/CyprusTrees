<?php

namespace Database\Seeders;

use App\Models\Neighborhood;
use Illuminate\Database\Seeder;

class NeighborhoodsTableSeeder extends Seeder
{
    public function run(): void
    {
        $city     = 'Nicosia';
        $district = 'Nicosia';

        $neighborhoods = [
            [
                'name'      => 'Within the Walls (Old Town)',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Strovolos',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Engomi',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Aglandjia',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Kaimakli',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Pallouriotissa',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Agioi Omologites',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Lykavittos',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Makedonitissa',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
            [
                'name'      => 'Anthoupoli (Strovolos)',
                'city'      => $city,
                'district'  => $district,
                'geom_ref'  => null,
            ],
        ];

        foreach ($neighborhoods as $n) {
            Neighborhood::firstOrCreate(['name' => $n['name']], $n);
        }
    }
}
