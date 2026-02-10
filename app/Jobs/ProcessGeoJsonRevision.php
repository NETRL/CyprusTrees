<?php

namespace App\Jobs;

use App\Models\GisFeature;
use App\Models\GisRevision;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isArray;

class ProcessGeoJsonRevision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $revisionId) {}

    public function handle(): void
    {
        $revision = GisRevision::query()->with('layer')->findOrFail($this->revisionId);

        if (!$revision->stored_path || !is_string($revision->stored_path)) {
            $revision->update([
                'status' => GisRevision::STATUS_FAILED,
                'error' => 'Missing stored_path for revision. Upload may have failed or was not saved.',
                'is_included' => false,
            ]);
            return;
        }

        if ($revision->status !== GisRevision::STATUS_QUEUED) {
            return;
        }

        $revision->update(['status' => GisRevision::STATUS_PROCESSING, 'error' => null]);

        try {
            $raw = Storage::disk('local')->get($revision->stored_path);
            $json = json_decode($raw, true);

            // Strip UTF-8 BOM if present
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

            $json = json_decode($raw, true);

            $sourceSrid = $this->detectSourceSrid($json);

            if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
            }

            if (!is_array($json)) {
                throw new \RuntimeException('Invalid GeoJSON: decoded value is not an object.');
            }

            if (($json['type'] ?? null) !== 'FeatureCollection') {
                $t = $json['type'] ?? 'null';
                throw new \RuntimeException("GeoJSON type must be FeatureCollection, got: {$t}");
            }

            $features = $json['features'] ?? null;
            if (!is_array($features)) {
                throw new \RuntimeException('GeoJSON FeatureCollection has no valid features array.');
            }

            // Remove previous features for this reveision (retries)
            GisFeature::query()->where('revision_id', $revision->id)->delete();

            $counts = [];
            $total = 0;

            $bbox = null; // [minLon,minLat,maxLon,maxLat]
            $batch = [];
            $batchSize = 500;

            foreach ($features as $f) {
                if (!is_array($f) || ($f['type'] ?? null) !== 'Feature') continue;

                $geom = $f['geometry'] ?? null;
                if (!is_array($geom) || !isset($geom['type'], $geom['coordinates'])) continue;

                $geomType = strtoupper($geom['type']);

                // normalization: treat GeometryCollection as unsupported for now
                if ($geomType === 'GEOMETRYCOLLECTION') continue;

                $props = $f['properties'] ?? null;
                if (!is_array($props)) $props = null;

                $geomJson = json_encode($geom, JSON_UNESCAPED_UNICODE);

                // update bbox roughly by scanning coordinates
                $bbox = $this->expandBbox($bbox, $geom['coordinates']);
                $batch[] = [
                    'layer_id' => $revision->layer_id,
                    'revision_id' => $revision->id,
                    'geom_type' => $geomType,
                    'properties' => $props ? json_encode($props, JSON_UNESCAPED_UNICODE) : null,
                    // PostGIS: ST_GeomFromGeoJSON works with {"type":"Point","coordinates":[...]}
                    'geom_json' => $geomJson,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $counts[$geomType] = ($counts[$geomType] ?? 0) + 1;
                $total++;

                if (count($batch) >= $batchSize) {
                    $this->flushBatch($batch, $sourceSrid);
                    $batch = [];
                }
            }

            if ($batch) $this->flushBatch($batch, $sourceSrid);

            $revision->update([
                'status' => GisRevision::STATUS_COMPLETED,
                'features_imported' => $total,
                'feature_counts' => $counts,
                'bbox' => $bbox,
            ]);

            // Activation logic
            // if this revision import_mode = replace, auto-set as baseline
            if ($revision->import_mode === GisRevision::MODE_REPLACE) {
                DB::transaction(function () use ($revision) {
                    GisRevision::query()
                        ->where('layer_id', $revision->layer_id)
                        ->where('id', '!=', $revision->id)
                        ->update(['is_included' => false]);

                    $revision->update(['is_included' => true, 'activated_at' => now()]);
                    $revision->layer->update(['active_revision_id' => $revision->id]);
                });
            } else {
                // Mode Append
                $revision->update(['is_included' => true]);
            }
        } catch (\Throwable $e) {
            $revision->update([
                'status' => GisRevision::STATUS_FAILED,
                'error' => $e->getMessage(),
                'is_included' => false,
            ]);
            throw $e;
        }
    }

    private function flushBatch(array $batch, int $sourceSrid): void
    {
        if (empty($batch)) return;

        foreach ($batch as $r) {
            // $r['properties'] is already either array|null or JSON string depending on how you built the batch.
            // Normalize to JSON string (or null) and let Postgres cast to jsonb.
            $props = null;

            if (array_key_exists('properties', $r) && $r['properties'] !== null) {
                if (is_array($r['properties'])) {
                    $props = json_encode($r['properties'], JSON_UNESCAPED_UNICODE);
                } elseif (is_string($r['properties'])) {
                    // validate it is JSON; if it's not, treat as null (or throw)
                    $decoded = json_decode($r['properties'], true);
                    $props = (json_last_error() === JSON_ERROR_NONE)
                        ? json_encode($decoded, JSON_UNESCAPED_UNICODE)
                        : null;
                }
            }

            DB::statement(
                "INSERT INTO gis_features (layer_id, revision_id, geom_type, properties, geom, created_at, updated_at ) 
                VALUES ( ?, ?, ?, ?::jsonb, ST_Transform( ST_SetSRID( ST_Force2D(ST_GeomFromGeoJSON(?)), ? ), 4326 ), ?, ? )",
                [
                    $r['layer_id'],
                    $r['revision_id'],
                    $r['geom_type'],
                    $props,
                    $r['geom_json'],
                    $sourceSrid,
                    $r['created_at'],
                    $r['updated_at'],
                ]
            );
        }
    }


    private function detectSourceSrid(array $geojson): int
    {
        // Default GeoJSON assumption
        $default = 4326;

        $crs = $geojson['crs']['properties']['name'] ?? null;
        if (!$crs || !is_string($crs)) {
            return $default;
        }

        // Common patterns:
        // urn:ogc:def:crs:EPSG::6312
        // EPSG:6312
        // urn:ogc:def:crs:EPSG:6.12:6312
        if (preg_match('/EPSG[:]{1,2}(\d+)/i', $crs, $m)) {
            return (int) $m[1];
        }

        return $default;
    }


    private function escapeSql(string $s): string
    {
        return str_replace("'", "''", $s);
    }

    private function expandBbox(?array $bbox, $coords): ?array
    {
        // coords can be nested; walk recursively to find lon/lat pairs
        $stack = [$coords];
        while ($stack) {
            $c = array_pop($stack);
            if (!is_array($c)) continue;

            // lon/lat pair
            if (count($c) >= 2 && is_numeric($c[0]) && is_numeric($c[1])) {
                $lon = (float)$c[0];
                $lat = (float)$c[1];
                if ($bbox === null) $bbox = [$lon, $lat, $lon, $lat];
                else {
                    $bbox[0] = min($bbox[0], $lon);
                    $bbox[1] = min($bbox[1], $lat);
                    $bbox[2] = max($bbox[2], $lon);
                    $bbox[3] = max($bbox[3], $lat);
                }
                continue;
            }

            foreach ($c as $child) $stack[] = $child;
        }
        return $bbox;
    }
}
