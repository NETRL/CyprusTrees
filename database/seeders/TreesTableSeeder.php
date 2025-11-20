<?php

namespace Database\Seeders;

use App\Models\Tree;
use App\Models\Species;
use App\Models\Neighborhood;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TreesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Map species by common_name => id
        $species = Species::pluck('id', 'common_name');
        // Map neighborhoods by name => id
        $neighborhoods = Neighborhood::pluck('id', 'name');

        // Guard: if nothing exists yet, don’t crash
        if ($species->isEmpty() || $neighborhoods->isEmpty()) {
            $this->command?->warn('Species or Neighborhoods table empty. Run SpeciesTableSeeder and NeighborhoodsTableSeeder first.');
            return;
        }

        $today = Carbon::today();

        $trees = [
            // Within the Walls
            [
                'species_id'         => $species['Olive'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Within the Walls (Old Town)'] ?? null,
                'lat'                => 35.171200,
                'lon'                => 33.362000,
                'address'            => 'Near Eleftheria Square, Within the Walls, Nicosia',
                'planted_at'         => $today->copy()->subYears(30),
                'status'             => 'existing',
                'health_status'      => 'healthy',
                'height_m'           => 7.5,
                'dbh_cm'             => 35.0,
                'canopy_diameter_m'  => 6.0,
                'last_inspected_at'  => $today->copy()->subMonths(3),
                'owner_type'         => 'municipal',
                'source'             => 'baseline_import',
            ],
            [
                'species_id'         => $species['Bitter orange'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Within the Walls (Old Town)'] ?? null,
                'lat'                => 35.169800,
                'lon'                => 33.365500,
                'address'            => 'Ledra Street, Within the Walls, Nicosia',
                'planted_at'         => $today->copy()->subYears(10),
                'status'             => 'existing',
                'health_status'      => 'healthy',
                'height_m'           => 4.0,
                'dbh_cm'             => 18.0,
                'canopy_diameter_m'  => 3.0,
                'last_inspected_at'  => $today->copy()->subMonths(1),
                'owner_type'         => 'municipal',
                'source'             => 'field_survey',
            ],

            // Strovolos
            [
                'species_id'         => $species['Aleppo pine'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Strovolos'] ?? null,
                'lat'                => 35.145000,
                'lon'                => 33.348000,
                'address'            => 'Strovolos Municipal Park, Strovolos',
                'planted_at'         => $today->copy()->subYears(20),
                'status'             => 'existing',
                'health_status'      => 'healthy',
                'height_m'           => 12.0,
                'dbh_cm'             => 40.0,
                'canopy_diameter_m'  => 8.0,
                'last_inspected_at'  => $today->copy()->subMonths(6),
                'owner_type'         => 'municipal',
                'source'             => 'baseline_import',
            ],
            [
                'species_id'         => $species['Carob'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Strovolos'] ?? null,
                'lat'                => 35.144500,
                'lon'                => 33.345500,
                'address'            => 'Residential street in Strovolos',
                'planted_at'         => $today->copy()->subYears(8),
                'status'             => 'existing',
                'health_status'      => 'stressed',
                'height_m'           => 5.0,
                'dbh_cm'             => 20.0,
                'canopy_diameter_m'  => 4.0,
                'last_inspected_at'  => $today->copy()->subWeeks(2),
                'owner_type'         => 'municipal',
                'source'             => 'citizen_report',
            ],

            // Engomi
            [
                'species_id'         => $species['Jacaranda'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Engomi'] ?? null,
                'lat'                => 35.165000,
                'lon'                => 33.312000,
                'address'            => 'Street near University of Nicosia, Engomi',
                'planted_at'         => $today->copy()->subYears(12),
                'status'             => 'existing',
                'health_status'      => 'healthy',
                'height_m'           => 6.0,
                'dbh_cm'             => 22.0,
                'canopy_diameter_m'  => 5.0,
                'last_inspected_at'  => $today->copy()->subMonths(2),
                'owner_type'         => 'municipal',
                'source'             => 'field_survey',
            ],
            [
                'species_id'         => $species['Stone pine'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Engomi'] ?? null,
                'lat'                => 35.166200,
                'lon'                => 33.314800,
                'address'            => 'Park in Engomi',
                'planted_at'         => $today->copy()->subYears(18),
                'status'             => 'existing',
                'health_status'      => 'watch',
                'height_m'           => 10.0,
                'dbh_cm'             => 35.0,
                'canopy_diameter_m'  => 7.5,
                'last_inspected_at'  => $today->copy()->subMonths(4),
                'owner_type'         => 'municipal',
                'source'             => 'field_survey',
            ],

            // Aglandjia
            [
                'species_id'         => $species['Eucalyptus'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Aglandjia'] ?? null,
                'lat'                => 35.148500,
                'lon'                => 33.393000,
                'address'            => 'Athalassa Park edge, Aglandjia',
                'planted_at'         => $today->copy()->subYears(25),
                'status'             => 'existing',
                'health_status'      => 'healthy',
                'height_m'           => 15.0,
                'dbh_cm'             => 45.0,
                'canopy_diameter_m'  => 9.0,
                'last_inspected_at'  => $today->copy()->subMonths(5),
                'owner_type'         => 'state',
                'source'             => 'baseline_import',
            ],
            [
                'species_id'         => $species['Olive'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Aglandjia'] ?? null,
                'lat'                => 35.147800,
                'lon'                => 33.390000,
                'address'            => 'Residential street in Aglandjia',
                'planted_at'         => $today->copy()->subYears(5),
                'status'             => 'existing',
                'health_status'      => 'healthy',
                'height_m'           => 3.5,
                'dbh_cm'             => 14.0,
                'canopy_diameter_m'  => 2.8,
                'last_inspected_at'  => $today->copy()->subWeeks(1),
                'owner_type'         => 'private',
                'source'             => 'citizen_report',
            ],

            // Kaimakli
            [
                'species_id'         => $species['Tamarisk'] ?? $species->first(),
                'neighborhood_id'    => $neighborhoods['Kaimakli'] ?? null,
                'lat'                => 35.179000,
                'lon'                => 33.389000,
                'address'            => 'Main road in Kaimakli',
                'planted_at'         => $today->copy()->subYears(15),
                'status'             => 'existing',
                'health_status'      => 'stressed',
                'height_m'           => 4.5,
                'dbh_cm'             => 19.0,
                'canopy_diameter_m'  => 3.5,
                'last_inspected_at'  => $today->copy()->subMonths(2),
                'owner_type'         => 'municipal',
                'source'             => 'field_survey',
            ],
        ];

        foreach ($trees as $data) {
            Tree::create($data);
        }
    }
}
