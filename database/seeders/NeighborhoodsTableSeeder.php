<?php

namespace Database\Seeders;

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
            'NIC_KAIMAKLI'        => 'NIC_KAIMAKLI.json',
            'NIC_PALLOURIOTISSA'  => 'NIC_PALLOURIOTISSA.json',
            'NIC_AYIOI_OMOLOYITES' => 'NIC_AYIOI_OMOLOYITES.json',
        ];

        foreach ($geomFiles as $geomRef => $filename) {
            $path = base_path('geojson-data/' . $filename);

            if (! file_exists($path)) {
                $this->command?->warn("GeoJSON file missing for {$geomRef}: {$path}");
                continue;
            }

            $raw = file_get_contents($path);
            if ($raw === false) {
                $this->command?->warn("Could not read GeoJSON file for {$geomRef}: {$path}");
                continue;
            }


            $decoded = json_decode($raw, true);
            if (! $decoded) {
                $this->command?->warn("Invalid JSON in {$path}");
                continue;
            }

            // Extract geometry depending on GeoJSON type
            $geometry = $this->extractGeometry($decoded);
            if (! $geometry) {
                $this->command?->warn("No geometry found in {$path}");
                continue;
            }

            // Encode only the geometry object (Polygon/MultiPolygon/etc.)
            $geometryJson = json_encode($geometry);

            // Use parameter binding to avoid quote/escape issues
            DB::statement(
                'UPDATE neighborhoods
                 SET geom = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)
                 WHERE geom_ref = ?',
                [$geometryJson, $geomRef]
            );
        }
    }

    /**
     * Extract the "geometry" part from GeoJSON that might be:
     * - a Geometry object
     * - a Feature
     * - a FeatureCollection (first feature)
     */
    protected function extractGeometry(array $geojson): ?array
    {
        if (! isset($geojson['type'])) {
            return null;
        }

        switch ($geojson['type']) {
            case 'Feature':
                return $geojson['geometry'] ?? null;

            case 'FeatureCollection':
                if (! empty($geojson['features'][0]['geometry'])) {
                    return $geojson['features'][0]['geometry'];
                }
                return null;

                // Geometry types: Polygon, MultiPolygon, LineString, etc.
            case 'Polygon':
            case 'MultiPolygon':
            case 'Point':
            case 'MultiPoint':
            case 'LineString':
            case 'MultiLineString':
                return $geojson;

            default:
                return null;
        }
    }
}
