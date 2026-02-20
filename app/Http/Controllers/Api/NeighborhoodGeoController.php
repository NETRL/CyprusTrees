<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class NeighborhoodGeoController extends Controller
{
    public function index(): JsonResponse
    {
        // Fetch from PostGIS
        $rows = DB::table('neighborhoods')
            ->select([
                'id',
                'name',
                'geom_ref',
                DB::raw('ST_AsGeoJSON(geom) as geom_geojson'),
            ])
            ->whereNotNull('geom')
            ->get();

        $features = [];

        foreach ($rows as $row) {
            if (! $row->geom_geojson) {
                continue;
            }

            $geometry = json_decode($row->geom_geojson, true);

            $features[] = [
                'type'       => 'Feature',
                'properties' => [
                    'id'       => $row->id,
                    'name'     => $row->name,
                    'geom_ref' => $row->geom_ref,
                ],
                'geometry'   => $geometry,
            ];
        }

        $featureCollection = [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];

        return response()->json($featureCollection);
    }

    public function showStats($id): JsonResponse
    {
        // 1. Basic Tree Counts
        $totalTrees = DB::table('trees')->where('neighborhood_id', $id)->count();


        // 2. Average Canopy
        $avgCanopy = DB::table('trees')
            ->where('neighborhood_id', $id)
            ->avg('canopy_diameter_m');

        // 3. Species Diversity (Top 3)
        $speciesStats = DB::table('trees')
            ->join('species', 'trees.species_id', '=', 'species.id')
            ->select('species.common_name', DB::raw('count(*) as total'))
            ->where('trees.neighborhood_id', $id)
            ->groupBy('species.common_name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $formattedSpecies = $speciesStats->map(function ($s) use ($totalTrees) {
            return [
                'name' => $s->common_name,
                'percentage' => $totalTrees > 0 ? round(($s->total / $totalTrees) * 100) : 0
            ];
        });

        // 4. Health Stats
        $goodHealth = DB::table('trees')
            ->where('neighborhood_id', $id)
            ->whereIn('health_status', ['good', 'excellent'])
            ->count();

        // 5. Open Reports (Geo-spatial query or relationship)
        // Assuming reports don't have neighborhood_id directly, we check reports linked to trees in this neighborhood
        $openReports = DB::table('citizen_reports')
            ->join('trees', 'citizen_reports.tree_id', '=', 'trees.id')
            ->where('trees.neighborhood_id', $id)
            ->where('citizen_reports.status', 'open')
            ->count();

        $maintenance_cost_ytd = DB::table('maintenance_events')
            ->join('trees', 'maintenance_events.tree_id', '=', 'trees.id')
            ->where('trees.neighborhood_id', $id)
            ->whereYear('maintenance_events.performed_at', date('Y')) // Filters for current year
            ->sum('maintenance_events.cost');

        return response()->json(
            [
                'total_trees' => $totalTrees,
                'avg_canopy' => round($avgCanopy, 2),
                'top_species' => $formattedSpecies,
                'health_good_pct' => $totalTrees > 0 ? round(($goodHealth / $totalTrees) * 100) : 0,
                'health_poor_pct' => $totalTrees > 0 ? round((($totalTrees - $goodHealth) / $totalTrees) * 100) : 0,
                'open_reports' => $openReports,
                // Example of fetching last planting event
                'last_planted_at' => DB::table('planting_events')
                    ->where('planting_events.neighborhood_id', $id)
                    ->max('planting_events.completed_at'),
                'maintenance_cost_ytd' => $maintenance_cost_ytd
            ]
        )->header('Cache-Control', 'private, max-age=60');
    }
}
