<?php

namespace App\Http\Controllers;

use App\Helpers\TreeHelper;
use App\Models\Tree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreesGeoController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = Tree::with(['species', 'neighborhood:id,name,city,district', 'citizenReports'])
            ->select([
                '*',
                DB::raw('ST_AsGeoJSON(geom) as geom_point'),
            ])
            ->whereNotNull('geom')
            ->get();

        $features = [];

        foreach ($rows as $row) {
            if (! $row->geom_point) {
                continue;
            }

            $geometry = json_decode($row->geom_point, true);

            $features[] = [
                'type'       => 'Feature',
                'properties' => $this->mapTreeToProperties($row),
                'geometry'   => $geometry,
            ];
        }

        $featureCollection = [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];

        return response()->json($featureCollection);
    }


    public function show(int $treeId): JsonResponse
    {
        $tree = Tree::with(['species', 'neighborhood:id,name,city,district', 'citizenReports'])
            ->select([
                '*',
                DB::raw('ST_AsGeoJSON(geom) as geom_point'),
            ])
            ->where('id', $treeId)
            ->whereNotNull('geom')
            ->firstOrFail();

        $properties = $this->mapTreeToProperties($tree);

        return response()->json($properties);
    }



    private function mapTreeToProperties(Tree $row): array
    {
        return [
            'id'                     => $row->id,
            'species_id'             => $row->species_id,
            'neighborhood_id'        => $row->neighborhood_id,
            'lat'                    => $row->lat,
            'lon'                    => $row->lon,
            'address'                => $row->address,
            'planted_at'             => $row->planted_at,
            'status'                 => $row->status,
            'health_status'          => $row->health_status,
            'sex'                    => $row->sex,
            'height_m'               => $row->height_m,
            'dbh_cm'                 => $row->dbh_cm,
            'canopy_diameter_m'      => $row->canopy_diameter_m,
            'last_inspected_at'      => $row->last_inspected_at,
            'owner_type'             => $row->owner_type,
            'source'                 => $row->source,
            'neighborhood'           => $row->neighborhood,
            'species'                => $row->species,
            'species_origin'         => $row->species['origin'] ?? null,
            'species_drought_tolerance' => $row->species['drought_tolerance'] ?? null,
            'species_canopy_class'   => $row->species['canopy_class'] ?? null,
            'calculated_pollen_risk' => isset($row->species['opals_score'], $row->sex)
                ? TreeHelper::calculateIAPS($row->species['opals_score'], $row->sex)
                : null,
            'citizenReports'         => $row->citizenReports,
        ];
    }
}
