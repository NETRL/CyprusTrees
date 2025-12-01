<?php

namespace Database\Seeders;

use App\Enums\CanopyClass;
use App\Enums\DroughtTolerance;
use App\Enums\SpeciesOrigin;
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
                'origin'            => SpeciesOrigin::NATIVE,
                'opals_score'       => 9,   
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Origin: Native to the Mediterranean. Pollen Risk: High (OPALS 9). Water Use: Very low (Drought Tolerant: High). Shade: Medium. Traditional tree, long-lived, and culturally significant.',
            ],
            [
                'latin_name'        => 'Ceratonia siliqua',
                'common_name'       => 'Carob',
                'family'            => 'Fabaceae',
                'origin'            => SpeciesOrigin::NATIVE,
                'opals_score'       => 1,   
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Origin: Native to the Eastern Mediterranean. Pollen Risk: Very Low (OPALS 1). Water Use: Very low (Drought Tolerant: High). Shade: Medium. Evergreen, excellent shade, and highly resilient to heat.',
            ],
            [
                'latin_name'        => 'Pinus halepensis',
                'common_name'       => 'Aleppo pine',
                'family'            => 'Pinaceae',
                'origin'            => SpeciesOrigin::NATIVE,
                'opals_score'       => 4,   
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Origin: Native to the Mediterranean. Pollen Risk: Low (OPALS 4). Water Use: Very low (Drought Tolerant: High). Shade: Large. Common in landscapes, releases large amounts of non-allergenic pollen.',
            ],
            [
                'latin_name'        => 'Pinus pinea',
                'common_name'       => 'Stone pine',
                'family'            => 'Pinaceae',
                'origin'            => SpeciesOrigin::NATIVE,
                'opals_score'       => 3,   
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Origin: Native to the Mediterranean. Pollen Risk: Low (OPALS 3). Water Use: Moderate. Shade: Large. Distinctive umbrella-shaped crown, provides pine nuts.',
            ],
            [
                'latin_name'        => 'Platanus orientalis',
                'common_name'       => 'Oriental plane',
                'family'            => 'Platanaceae',
                'origin'            => SpeciesOrigin::NATIVE,
                'opals_score'       => 8,   
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Origin: Native to the East Mediterranean. Pollen Risk: High (OPALS 8). Water Use: Moderate (Prefers irrigation). Shade: Large. Excellent street tree, but pollen is a strong seasonal allergen.',
            ],
            [
                'latin_name'        => 'Cupressus sempervirens',
                'common_name'       => 'Italian cypress',
                'family'            => 'Cupressaceae',
                'origin'            => SpeciesOrigin::NATIVE,
                'opals_score'       => 7,   
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::S,
                'notes'             => 'Origin: Native to the East Mediterranean. Pollen Risk: High (OPALS 7). Water Use: Very low (Drought Tolerant: High). Shade: Small/Narrow. Iconic columnar shape, notorious for its winter-released allergenic pollen.',
            ],
            [
                'latin_name'        => 'Eucalyptus spp.',
                'common_name'       => 'Eucalyptus',
                'family'            => 'Myrtaceae',
                'origin'            => SpeciesOrigin::EXOTIC,
                'opals_score'       => 5,   
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::L,
                'notes'             => 'Origin: Exotic (Australia). Pollen Risk: Moderate (OPALS 5). Water Use: Moderate. Shade: Large. Fast-growing, widely planted in the 20th century.',
            ],
            [
                'latin_name'        => 'Jacaranda mimosifolia',
                'common_name'       => 'Jacaranda',
                'family'            => 'Bignoniaceae',
                'origin'            => SpeciesOrigin::EXOTIC,
                'opals_score'       => 2,   
                'drought_tolerance' => DroughtTolerance::MODERATE,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Origin: Exotic (South America). Pollen Risk: Very Low (OPALS 2). Water Use: Moderate. Shade: Medium. Highly ornamental with purple flowers, low allergy risk.',
            ],
            [
                'latin_name'        => 'Citrus aurantium',
                'common_name'       => 'Bitter orange',
                'family'            => 'Rutaceae',
                'origin'            => SpeciesOrigin::EXOTIC,
                'opals_score'       => 1,   
                'drought_tolerance' => DroughtTolerance::LOW,
                'canopy_class'      => CanopyClass::S,
                'notes'             => 'Origin: Exotic (Southeast Asia). Pollen Risk: Very Low (OPALS 1). Water Use: High (Drought Tolerant: Low). Shade: Small. Fragrant flowers, heavy insect-pollinated, requires regular watering.',
            ],
            [
                'latin_name'        => 'Tamarix spp.',
                'common_name'       => 'Tamarisk',
                'family'            => 'Tamaricaceae',
                'origin'            => SpeciesOrigin::NATIVE,
                'opals_score'       => 3,   
                'drought_tolerance' => DroughtTolerance::HIGH,
                'canopy_class'      => CanopyClass::M,
                'notes'             => 'Origin: Native to the Mediterranean. Pollen Risk: Low (OPALS 3). Water Use: Very low (Drought Tolerant: High). Shade: Medium. Extremely resilient and salt-tolerant, good for exposed sites.',
            ],
        ];


        foreach ($species as $s) {
            Species::firstOrCreate(['common_name' => $s['common_name']], $s);
        }
    }
}