<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function __construct()
    {
        // throw new \Exception('Not implemented');
    }



    public function index()
    {
        $maintenanceCalendarEvents = MaintenanceEvent::query()
            ->with([
                'tree:id,address',
                'type:type_id,name',
                'performer:id,first_name,last_name',
            ])
            ->whereNotNull('performed_at')
            ->orderBy('performed_at', 'asc')
            ->get()
            ->map(function (MaintenanceEvent $event) {
                $performedAt = $event->performed_at?->toIso8601String();

                return [
                    'id'           => $event->event_id,                     // your PK
                    'tree_id'      => $event->tree_id,
                    'type_id'      => $event->type_id,
                    'type_name'    => optional($event->type)->name,
                    'performed_at' => $performedAt,
                    'performer'    => $event->performer
                        ? trim($event->performer->first_name . ' ' . $event->performer->last_name)
                        : null,
                    'quantity'     => $event->quantity,
                    'cost'         => $event->cost,
                    'notes'        => $event->notes,
                    // You can prebuild a nice label for the calendar:
                    'title'        => sprintf(
                        '%s – Tree #%d',
                        optional($event->type)->name ?? 'Maintenance',
                        $event->tree_id
                    ),
                ];
            });

        return Inertia::render('Calendar/Index3', [
            // 'maintenanceCalendarEvents' => $maintenanceCalendarEvents,
        ]);
    }
}
