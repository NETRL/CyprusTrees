<?php

namespace App\Http\Controllers;

use App\Models\Neighborhood;
use App\Models\PlantingEvent;
use App\Models\ReportType;
use App\Models\Species;
use App\Models\Tag;
use App\Models\Tree;
use App\Services\UserEventsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index(Request $request, UserEventsService $svc)
    {
        $initialTreeId = $request->filled('tree_id') ? (int) $request->input('tree_id') : null;
        $requestedLat = $request->filled('lat') ? (float) $request->input('lat') : null;
        $requestedLon = $request->filled('lon') ? (float) $request->input('lon') : null;

        $initialMode = $request->string('mode')->toString() ?: 'default';
        $initialEventId = $request->filled('event_id') ? (int) $request->input('event_id') : null;

        $initialLocation = [
            'lat' => $requestedLat,
            'lon' => $requestedLon,
        ];

        $userEvents = [];
        if ($request->user()?->id !== null) {
            $userEvents = $svc->forUser($request->user()->id, now());
        }


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
            'initialMode' => $initialMode,
            'initialEventId' => $initialEventId,
            'userEvents' => $userEvents,
            'plantingEvents' => $this->getPlantingEvents(),
        ]);
        
    }

    function getPlantingEvents(): Collection{

        $events = PlantingEvent::query()
        ->where('completed_at', '!=', null)
        ->with(['eventTrees', 'campaign', ])
        ->get(['planting_id', 'campaign_id', 'completed_at', 'lat', 'lon', 'target_tree_count', 'status']);

        return $events ?? [];
    }
}
