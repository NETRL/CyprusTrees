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
}
