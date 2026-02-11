<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GisLayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GisLayerApiController extends Controller
{
    public function layers(Request $request)
    {
        $layers = GisLayer::query()
            ->where('is_active', true)
            ->whereHas('includeRevisions')
            ->with([
                'includeRevisions:id,layer_id,revision_no,label,bbox,centroid,features_imported,feature_counts,updated_at',
            ])
            ->orderByRaw("coalesce(category,'') asc")
            ->orderBy('display_name')
            ->get()
            ->map(function ($l) {
                $meta = $l->metadata ?? [];

                $revisions = $l->includeRevisions->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'revision_no' => $r->revision_no,
                        'label' => $r->label,
                        'bbox' => $r->bbox,
                        'features_imported' => (int) $r->features_imported,
                        'feature_counts' => $r->feature_counts,
                        'centroid' => null, // keep null unless you explicitly select ST_AsGeoJSON(centroid)
                        'updated_at' => optional($r->updated_at)->toISOString(),
                    ];
                })->values();

                // Optional: compute a combined bbox for "zoom to layer"
                $combinedBbox = $this->combineBboxes($revisions->pluck('bbox')->all());

                return [
                    'id' => $l->id,
                    'key' => $l->key,
                    'name' => $l->name,
                    'display_name' => $l->display_name,
                    'category' => $l->category,
                    'source' => $l->source,
                    'is_editable' => (bool) $l->is_editable,
                    'is_active' => (bool) $l->is_active,
                    'color' => $meta['color'] ?? '#3b82f6',

                    // NEW: array of included revisions
                    'revisions' => $revisions,

                    // NEW: convenience for UI (optional)
                    'bbox' => $combinedBbox,
                    'total_features' => (int) $l->includeRevisions->sum('features_imported'),
                ];
            });

        return response()->json(['layers' => $layers]);
    }

    /**
     * GET /api/gis-map/layers/{key}/features
     *
     * Returns features across ALL included revisions by default.
     * Optional query params:
     * - revision_ids[]=1&revision_ids[]=2  (limit to specific included revisions)
     * - bbox=minLon,minLat,maxLon,maxLat
     * - limit=50000
     */
    public function features(Request $request, string $key)
    {
        $layer = GisLayer::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->with(['includeRevisions:id,layer_id']) // all included revision ids
            ->firstOrFail();

        $includedRevisionIds = $layer->includeRevisions->pluck('id')->values()->all();

        if (empty($includedRevisionIds)) {
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => [],
            ]);
        }

        // Optional: caller can pass revision_ids[] to restrict (still must be included)
        $requestedIds = $request->input('revision_ids', null);
        $revisionIds = $includedRevisionIds;

        if (is_array($requestedIds) && count($requestedIds)) {
            $requestedIds = array_values(array_filter(array_map('intval', $requestedIds)));
            $revisionIds = array_values(array_intersect($includedRevisionIds, $requestedIds));
        }

        if (empty($revisionIds)) {
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => [],
            ]);
        }

        $limit = (int) $request->integer('limit', 20000);
        $limit = max(1, min($limit, 50000));

        $bbox = $this->parseBbox($request->query('bbox'));

        $q = DB::table('gis_features')
            ->select([
                'id',
                'revision_id',
                'geom_type',
                'properties',
                'source_feature_id',
                // DB::raw("ST_AsGeoJSON(ST_Transform(geom, 4326))::jsonb as geometry"),
                DB::raw("ST_AsGeoJSON(geom)::jsonb as geometry"),
            ])
            ->where('layer_id', $layer->id)
            ->whereIn('revision_id', $revisionIds);

        if ($bbox) {
            [$minLon, $minLat, $maxLon, $maxLat] = $bbox;

            $q->whereRaw(
                "geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)",
                [$minLon, $minLat, $maxLon, $maxLat]
            )->whereRaw(
                "ST_Intersects(geom, ST_MakeEnvelope(?, ?, ?, ?, 4326))",
                [$minLon, $minLat, $maxLon, $maxLat]
            );
        }

        $rows = $q->limit($limit)->get();

        $features = $rows->map(function ($r) {
            $geometry = $r->geometry;
            if (is_string($geometry)) $geometry = json_decode($geometry, true);

            $props = $r->properties ?? [];
            if (is_string($props)) $props = json_decode($props, true) ?? [];
            elseif (is_object($props)) $props = (array) $props;

            return [
                'type' => 'Feature',
                'id' => $r->id,
                'geometry' => $geometry,
                'properties' => array_merge($props, [
                    '_id' => $r->id,
                    '_revision_id' => $r->revision_id,
                    '_geom_type' => $r->geom_type,
                    '_source_feature_id' => $r->source_feature_id,
                ]),
            ];
        })->values()->all();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    private function parseBbox(?string $bbox): ?array
    {
        if (!$bbox) return null;

        $parts = array_map('trim', explode(',', $bbox));
        if (count($parts) !== 4) return null;

        $nums = array_map('floatval', $parts);
        if ($nums[0] >= $nums[2] || $nums[1] >= $nums[3]) return null;

        return $nums;
    }

    /**
     * Combine multiple [minLon,minLat,maxLon,maxLat] bboxes into one.
     */
    private function combineBboxes(array $bboxes): ?array
    {
        $bboxes = array_values(array_filter($bboxes, function ($b) {
            return is_array($b) && count($b) === 4;
        }));

        if (!$bboxes) return null;

        $minLon = $bboxes[0][0];
        $minLat = $bboxes[0][1];
        $maxLon = $bboxes[0][2];
        $maxLat = $bboxes[0][3];

        foreach ($bboxes as $b) {
            $minLon = min($minLon, $b[0]);
            $minLat = min($minLat, $b[1]);
            $maxLon = max($maxLon, $b[2]);
            $maxLat = max($maxLat, $b[3]);
        }

        return [$minLon, $minLat, $maxLon, $maxLat];
    }
}
