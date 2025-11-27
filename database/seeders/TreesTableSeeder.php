<?php

namespace Database\Seeders;

use App\Enums\HealthStatus;
use App\Enums\OwnerType;
use App\Enums\TreeSex;
use App\Enums\TreeStatus;
use App\Models\Tree;
use App\Models\Species;
use App\Models\Neighborhood;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TreesTableSeeder extends Seeder
{
    public function run(): void
    {
        $species = Species::pluck('id', 'common_name');
        $neighborhoods = Neighborhood::whereNotNull('geom')->get();

        if ($species->isEmpty() || $neighborhoods->isEmpty()) {
            $this->command?->warn("Species or Neighborhoods missing.");
            return;
        }

        $today = Carbon::today();

        // ENUM pools
        $sources = ['citizen_report', 'field_survey', 'baseline_import'];

        for ($i = 0; $i < 500; $i++) {

            $neighborhood = $neighborhoods->random();

            // --- Generate a random point inside the neighborhood polygon ---
            $point = DB::selectOne("
                SELECT ST_X(geom) AS lon, ST_Y(geom) AS lat
                FROM (
                    SELECT (ST_Dump(ST_GeneratePoints(geom, 1))).geom AS geom
                    FROM neighborhoods
                    WHERE id = ?
                ) AS sub
                LIMIT 1
            ", [$neighborhood->id]);

            if (!$point) {
                // fallback: skip
                continue;
            }

            $speciesId = $species->random();

            $treeSex = TreeSex::cases()[array_rand(TreeSex::cases())];
            $healthStatus = HealthStatus::cases()[array_rand(HealthStatus::cases())];
            $treeStatus = TreeStatus::cases()[array_rand(TreeStatus::cases())];
            $ownerType = OwnerType::cases()[array_rand(OwnerType::cases())];


            $plantedAt = $today->copy()->subYears(rand(3, 40));
            $lastInspected = $today->copy()->subDays(rand(10, 400));

            // Tree payload -----------------------------------------------
            $data = [
                'species_id'         => $speciesId,
                'neighborhood_id'    => $neighborhood->id,
                'lat'                => $point->lat,
                'lon'                => $point->lon,
                'address'            => $neighborhood->name,
                'planted_at'         => $plantedAt,
                'status'             => $treeStatus,
                'health_status'      => $healthStatus,
                'sex'                => $treeSex,
                'height_m'           => round(rand(20, 150) / 10, 1),  // 2.0–15.0m
                'dbh_cm'             => round(rand(10, 60), 1),
                'canopy_diameter_m'  => round(rand(15, 90) / 10, 1),
                'last_inspected_at'  => $lastInspected,
                'owner_type'         => $ownerType,
                'source'             => $sources[array_rand($sources)],
            ];

            // Add geometry
            $data['geom'] = DB::raw(
                "ST_SetSRID(ST_MakePoint({$data['lon']}, {$data['lat']}), 4326)"
            );

            Tree::create($data);
        }
    }
}
