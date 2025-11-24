<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeometryHelper
{

    public static function updateSpatialData($item)
    {
        $geomRef = $item->geom_ref;
        
        if (empty($geomRef)) {
            Log::warning("Neighborhood {$item->id} has no geom_ref, skipping spatial update.");
            return false;
        }

        $path = base_path('geojson-data/' . $geomRef . '.json');

        if (! file_exists($path)) {
            Log::warning("GeoJSON file missing for {$geomRef}: {$path}");
            return false;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            Log::warning("Could not read GeoJSON file for {$geomRef}: {$path}");
            return false;
        }


        $decoded = json_decode($raw, true);
        if (! $decoded) {
            Log::warning("Invalid JSON in {$path}");
            return false;
        }

        // Extract geometry depending on GeoJSON type
        $geometry = self::extractGeometry($decoded);
        if (! $geometry) {
            Log::warning("No geometry found in {$path}");
            return false;
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

        return true;
    }
    /**
     * Extract the "geometry" part from GeoJSON that might be:
     * - a Geometry object
     * - a Feature
     * - a FeatureCollection (first feature)
     */
    protected static function extractGeometry(array $geojson): ?array
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
