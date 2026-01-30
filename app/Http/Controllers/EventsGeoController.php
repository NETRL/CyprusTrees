<?php

namespace App\Http\Controllers;

use App\Models\PlantingEvent;
use Illuminate\Http\Request;

class EventsGeoController extends Controller
{
    public function showPlanting($id)
    {
        $planting = PlantingEvent::withCount('eventTrees')->find($id);

        // dd($planting);
        
        return response()->json($planting);
    }
}
