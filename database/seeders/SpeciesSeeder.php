<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        $species = [
            [
                'latin_name'        => 'Quercus robur',
                'common_name'       => 'English Oak',
                'family'            => 'Fagaceae',
                'drought_tolerance' => 'High',
                'canopy_class'      => 'L',
                'notes'             => 'Long-lived deciduous tree common in Europe.',
            ],
            [
                'latin_name'        => 'Pinus pinea',
                'common_name'       => 'Stone Pine',
                'family'            => 'Pinaceae',
                'drought_tolerance' => 'High',
                'canopy_class'      => 'M',
                'notes'             => 'Mediterranean conifer with umbrella-shaped canopy.',
            ],
            [
                'latin_name'        => 'Platanus × acerifolia',
                'common_name'       => 'London Plane',
                'family'            => 'Platanaceae',
                'drought_tolerance' => 'Moderate',
                'canopy_class'      => 'L',
                'notes'             => 'Hybrid species commonly used in cities.',
            ],
            [
                'latin_name'        => 'Acer campestre',
                'common_name'       => 'Field Maple',
                'family'            => 'Sapindaceae',
                'drought_tolerance' => 'Low',
                'canopy_class'      => 'M',
                'notes'             => 'Small to medium-sized tree tolerant of pruning.',
            ],
            [
                'latin_name'        => 'Cercis siliquastrum',
                'common_name'       => 'Judas Tree',
                'family'            => 'Fabaceae',
                'drought_tolerance' => 'Moderate',
                'canopy_class'      => 'S',
                'notes'             => 'Ornamental tree with pink spring blossoms.',
            ],
        ];

        foreach ($species as $data) {
            Species::updateOrCreate(
                ['latin_name' => $data['latin_name']],
                $data
            );
        }

        Species::factory()->count(15)->create();
    }
}
