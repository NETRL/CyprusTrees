<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\ReportType;
use App\Models\Species;
use App\Models\Tag;
use App\Models\Tree;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $initialTreeId = $request->filled('tree_id') ? (int) $request->input('tree_id') : null;
        $requestedLat = $request->filled('lat') ? (float) $request->input('lat') : null;
        $requestedLon = $request->filled('lon') ? (float) $request->input('lon') : null;

        $mode = $request->string('mode')->toString() ?: 'default';
        $eventId = $request->filled('event_id') ? (int) $request->input('event_id') : null;

        $initialLocation = [
            'lat' => $requestedLat,
            'lon' => $requestedLon,
        ];

        return Inertia::render('Map/MapView', [
            'reportTypes' => ReportType::all(),
            'speciesData' => Species::orderBy('common_name')
                ->get(['id', 'latin_name', 'common_name']),
            'neighborhoodData' => Neighborhood::orderBy('name')->get(['id', 'name', 'city']),
            'tagData' => Tag::all(),
            'treeSex' => Tree::getTreeSexOptions(),
            'healthStatus' => Tree::getHealthStatusOptions(),
            'treeStatus' => Tree::getTreeStatusOptions(),
            'ownerType' => Tree::getOwnerTypeOptions(),
            'initialTreeId' => $initialTreeId,
            'initialLocation' => $initialLocation,
            'mode' => $mode,
            'eventId' => $eventId,
        ]);
    }
}
