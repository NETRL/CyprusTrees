<?php

namespace Database\Seeders;

use App\Enums\CanopyClass;
use App\Enums\DroughtTolerance;
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
                'opals_score' => 9,   // Very high pollen allergen; major Mediterranean allergen
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Traditional Mediterranean tree, very drought tolerant, long-lived.',
            ],
            [
                'latin_name'        => 'Ceratonia siliqua',
                'common_name'       => 'Carob',
                'family'            => 'Fabaceae',
                'opals_score' => 1,   // Mostly insect-pollinated; low airborne pollen
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Evergreen, good shade, tolerates heat and dry conditions.',
            ],
            [
                'latin_name'        => 'Pinus halepensis',
                'common_name'       => 'Aleppo pine',
                'family'            => 'Pinaceae',
                'opals_score' => 4,   // Produces huge pollen quantity but weak allergenicity
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Common in Mediterranean landscapes; tall, conical crown.',
            ],
            [
                'latin_name'        => 'Pinus pinea',
                'common_name'       => 'Stone pine',
                'family'            => 'Pinaceae',
                'opals_score' => 3,   // Same family; high pollen volume but low allergenic effect
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Umbrella-shaped crown, good for large open spaces.',
            ],
            [
                'latin_name'        => 'Platanus orientalis',
                'common_name'       => 'Oriental plane',
                'family'            => 'Platanaceae',
                'opals_score' => 8,   // Strong seasonal allergen; triggers respiratory symptoms
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Large shade tree, prefers more water (river banks, irrigated areas).',
            ],
            [
                'latin_name'        => 'Cupressus sempervirens',
                'common_name'       => 'Italian cypress',
                'family'            => 'Cupressaceae',
                'opals_score' => 7,   // Notoriously allergenic in Mediterranean; long pollen season
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::S,
                'notes'             => 'Columnar form, often used as accent tree or for windbreaks.',
            ],
            [
                'latin_name'        => 'Eucalyptus spp.',
                'common_name'       => 'Eucalyptus',
                'family'            => 'Myrtaceae',
                'opals_score' => 5,   // Moderate; varies by species; some respiratory sensitivity
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Fast-growing, used historically for drainage/wet areas.',
            ],
            [
                'latin_name'        => 'Jacaranda mimosifolia',
                'common_name'       => 'Jacaranda',
                'family'            => 'Bignoniaceae',
                'opals_score' => 2,   // Low airborne pollen; mostly insect-pollinated
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Ornamental with purple flowers, used in streets/parks.',
            ],
            [
                'latin_name'        => 'Citrus aurantium',
                'common_name'       => 'Bitter orange',
                'family'            => 'Rutaceae',
                'opals_score' => 1,   // Very low; fragrant flowers but not a pollen allergen source
                'drought_tolerance' => DroughtTolerance::LOW,
                'canopy_class'      => CanopyClass::S,
                'notes'             => 'Street tree with fragrant flowers and ornamental fruits.',
            ],
            [
                'latin_name'        => 'Tamarix spp.',
                'common_name'       => 'Tamarisk',
                'family'            => 'Tamaricaceae',
                'opals_score' => 3,   // Light airy pollen but not associated with strong allergies
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Salt tolerant, often used in coastal / exposed locations.',
            ],
        ];


        foreach ($species as $s) {
            Species::firstOrCreate(['common_name' => $s['common_name']], $s);
        }
    }
}
