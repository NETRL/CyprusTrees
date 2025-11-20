<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesTableSeeder extends Seeder
{
    public function run(): void
    {
        $species = [
            [
                'latin_name'        => 'Olea europaea',
                'common_name'       => 'Olive',
                'family'            => 'Oleaceae',
                'drought_tolerance' => 'high',
                'canopy_class'      => 'M',
                'notes'             => 'Traditional Mediterranean tree, very drought tolerant, long-lived.',
            ],
            [
                'latin_name'        => 'Ceratonia siliqua',
                'common_name'       => 'Carob',
                'family'            => 'Fabaceae',
                'drought_tolerance' => 'high',
                'canopy_class'      => 'M',
                'notes'             => 'Evergreen, good shade, tolerates heat and dry conditions.',
            ],
            [
                'latin_name'        => 'Pinus halepensis',
                'common_name'       => 'Aleppo pine',
                'family'            => 'Pinaceae',
                'drought_tolerance' => 'high',
                'canopy_class'      => 'L',
                'notes'             => 'Common in Mediterranean landscapes; tall, conical crown.',
            ],
            [
                'latin_name'        => 'Pinus pinea',
                'common_name'       => 'Stone pine',
                'family'            => 'Pinaceae',
                'drought_tolerance' => 'moderate',
                'canopy_class'      => 'L',
                'notes'             => 'Umbrella-shaped crown, good for large open spaces.',
            ],
            [
                'latin_name'        => 'Platanus orientalis',
                'common_name'       => 'Oriental plane',
                'family'            => 'Platanaceae',
                'drought_tolerance' => 'moderate',
                'canopy_class'      => 'L',
                'notes'             => 'Large shade tree, prefers more water (river banks, irrigated areas).',
            ],
            [
                'latin_name'        => 'Cupressus sempervirens',
                'common_name'       => 'Italian cypress',
                'family'            => 'Cupressaceae',
                'drought_tolerance' => 'high',
                'canopy_class'      => 'S',
                'notes'             => 'Columnar form, often used as accent tree or for windbreaks.',
            ],
            [
                'latin_name'        => 'Eucalyptus spp.',
                'common_name'       => 'Eucalyptus',
                'family'            => 'Myrtaceae',
                'drought_tolerance' => 'moderate',
                'canopy_class'      => 'L',
                'notes'             => 'Fast-growing, used historically for drainage/wet areas.',
            ],
            [
                'latin_name'        => 'Jacaranda mimosifolia',
                'common_name'       => 'Jacaranda',
                'family'            => 'Bignoniaceae',
                'drought_tolerance' => 'moderate',
                'canopy_class'      => 'M',
                'notes'             => 'Ornamental with purple flowers, used in streets/parks.',
            ],
            [
                'latin_name'        => 'Citrus aurantium',
                'common_name'       => 'Bitter orange',
                'family'            => 'Rutaceae',
                'drought_tolerance' => 'low',
                'canopy_class'      => 'S',
                'notes'             => 'Street tree with fragrant flowers and ornamental fruits.',
            ],
            [
                'latin_name'        => 'Tamarix spp.',
                'common_name'       => 'Tamarisk',
                'family'            => 'Tamaricaceae',
                'drought_tolerance' => 'high',
                'canopy_class'      => 'M',
                'notes'             => 'Salt tolerant, often used in coastal / exposed locations.',
            ],
        ];

        foreach ($species as $s) {
            Species::firstOrCreate(['common_name' => $s['common_name']], $s);
        }
    }
}
