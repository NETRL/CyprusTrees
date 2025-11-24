<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreesGeoController extends Controller
{
    public function index(): JsonResponse
    {
        // Fetch from PostGIS
        $rows = Tree::with('species:id,common_name,latin_name')
        ->with('neighborhood:id,name,city,district')
            ->select([
                '*',
                DB::raw('ST_AsGeoJSON(geom) as geom_point'),
            ])
            ->whereNotNull('geom')
            ->get();

        $features = [];

        foreach ($rows as $row) {
            // dd($row);
            if (! $row->geom_point) {
                continue;
            }

            $geometry = json_decode($row->geom_point, true);

            $features[] = [
                'type'       => 'Feature',
                'properties' => [
                    'id'       => $row->id,
                    'species_id' => $row->species_id,
                    'neighborhood_id'     => $row->neighborhood_id,
                    'lat' => $row->lat,
                    'lon' => $row->lon,
                    'address' => $row->address,
                    'planted_at' => $row->planted_at,
                    'status' => $row->status,
                    'health_status' => $row->health_status,
                    'height_m' => $row->height_m,
                    'dbh_cm' => $row->dbh_cm,
                    'canopy_diameter_m' => $row->canopy_diameter_m,
                    'last_inspected_at' => $row->last_inspected_at,
                    'owner_type' => $row->owner_type,
                    'source' => $row->source,
                    'neighborhood' => $row->neighborhood,
                    'species' => $row->species,
                    
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
