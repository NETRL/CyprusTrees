<?php

namespace App\Http\Controllers;

use App\Enums\PlantingEventStatus;
use App\Models\MaintenanceEvent;
use App\Models\PlantingEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function __construct()
    {
        // throw new \Exception('Not implemented');
    }


    public function index(Request $request)
    {
        // $eventsData = $this->getEventsData($request);

        return Inertia::render('Calendar/Index', [
            // 'events' => $eventsData,
            'events' => [],
        ]);
    }

    public function getEvents(Request $request)
    {
        $eventsData = $this->getEventsData($request);
        return response()->json($eventsData);
    }

    protected function getEventsData(Request $request): array
    {

        $view = $request->string('view')->toString() ?: 'month';
        $date = $request->string('date')->toString(); // expects YYYY-MM-DD

        $base = $date ? Carbon::parse($date) : now();

        [$start, $end] = match ($view) {
            'day' => [$base->copy()->startOfDay(), $base->copy()->endOfDay()],
            'year' => [$base->copy()->startOfYear(), $base->copy()->endOfYear()],
            default => [$base->copy()->startOfMonth(), $base->copy()->endOfMonth()],
        };
        $user   = auth()->user();
        $userId = $user?->id;

        $isAdmin = $user?->hasRole('admin');

        /* Planting Events */
        $plantingQuery = PlantingEvent::with([
            'campaign:id,name,sponsor',
            'neighborhood:id,name',
            'assignedTo:id,first_name,last_name',
            'createdBy:id,first_name,last_name',
        ])
            ->withCount(['eventTrees'])
            // overlap filter: event intersects [start,end]
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('started_at', [$start, $end])
                    ->orWhereBetween('completed_at', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('started_at', '<=', $start)
                            ->where(function ($qqq) use ($end) {
                                $qqq->whereNull('completed_at')
                                    ->orWhere('completed_at', '>=', $end);
                            });
                    });
            });

        if (!$isAdmin && $userId) {
            $plantingQuery->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                    ->orWhere('created_by', $userId);
            });
        }

        $plantings = $plantingQuery->get()
            ->map(function (PlantingEvent $e) {

                $campaign  = $e->campaign;
                $assigned  = $e->assignedTo;
                $creator   = $e->createdBy;

                $campaignLabel = $campaign
                    ? ($campaign->name . ($campaign->sponsor ? " ({$campaign->sponsor})" : ''))
                    : '-';

                $assignedName = $assigned
                    ? trim(($assigned->first_name ?? '') . ' ' . ($assigned->last_name ?? ''))
                    : '-';

                $creatorName = $creator
                    ? trim(($creator->first_name ?? '') . ' ' . ($creator->last_name ?? ''))
                    : '-';

                $neighborhoodName = $e->name;

                $treesCount = (int) ($e->event_trees_count ?? 0);

                // Short title form month view
                $titleParts = ['Planting'];
                if ($neighborhoodName) $titleParts[] = $neighborhoodName;
                $title = implode(' - ', $titleParts);

                // Longer description for day view / details
                $desc = [];
                $desc[] = 'Status: ' . $e->status;
                if ($campaignLabel) $desc[] = 'Campaign: ' . $campaignLabel;
                if ($assignedName) $desc[] = 'Assigned to: ' . $assignedName;
                if ($creatorName)  $desc[] = 'Created by: ' . $creatorName;
                if (!is_null($e->target_tree_count)) {
                    $desc[] = "Trees: {$treesCount}/{$e->target_tree_count}";
                } else {
                    $desc[] = "Trees: {$treesCount}";
                }
                if ($e->notes) $desc[] = $e->notes;

                $description = implode("\n", array_filter($desc));

                // Calendar start/end:
                // If started_at missing, fallback to created_at
                $startIso = optional($e->started_at ?? $e->created_at)->toIso8601String();
                $endIso   = optional($e->completed_at)->toIso8601String();

                // Color by status
                $color = match($e->status){
                    PlantingEventStatus::DRAFT->value       => 'stone',
                    PlantingEventStatus::SCHEDULED->value   => 'indigo',
                    PlantingEventStatus::IN_PROGRESS->value => 'amber',
                    PlantingEventStatus::COMPLETED->value   => 'emerald',
                    PlantingEventStatus::CANCELLED->value   => 'red',
                    default                                 => 'default',
                };

                return [
                    'id'          => 'planting_event_' . $e->planting_id,
                    'event_type'  => 'planting_event',

                    // For filtering / ownership in UI
                    'assigned_to' => $e->assigned_to,
                    'created_by'  => $e->created_by,

                    'planting_id' => $e->planting_id,
                    'campaign_id' => $e->campaign_id,
                    'campaign'    => $campaign,
                    'neighborhood_id' => $e->neighborhood_id,
                    'neighborhood'    => $e->neighborhood,

                    'title'       => $title,
                    'start'       => $startIso,
                    'end'         => $endIso,           // optional (calendar can treat as all-day/single point)
                    'description' => $description !== '' ? $description : null,
                    'color'       => 'emerald',

                    'meta'        => [
                        'status'            => $e->status,
                        'trees_count'        => $treesCount,
                        'target_tree_count'  => $e->target_tree_count,
                        'assigned_to_name'   => $assignedName,
                        'creator_name'       => $creatorName,
                        'location'           => method_exists($e, 'getLocationAttribute') ? $e->getLocationAttribute() : null,
                        'lat'                => $e->lat,
                        'lon'                => $e->lon,
                    ],
                ];
            })
            ->filter()
            ->values()
            ->collect();


        /* Maintenance events */
        $maintenanceQuery = MaintenanceEvent::with([
            'tree.species',
            'tree.neighborhood',
            'type',
            'performer',
        ])
            ->whereBetween('performed_at', [$start, $end]);

        if (!$isAdmin && $userId) {
            $maintenanceQuery->where('performed_by', $userId);
        }

        $maintenance = $maintenanceQuery->get()
            ->map(function (MaintenanceEvent $m) {
                if (!$m->tree || !$m->tree->species || !$m->type) {
                    return null;
                }

                $tree      = $m->tree;
                $species   = $tree->species;
                $type      = $m->type;
                $performer = $m->performer;

                $treeLabel = $species->common_name
                    . ' (' . $species->latin_name . ')';

                // color by maintenance type
                $color = match ($m->type->name) {
                    'Prune'     => 'amber', // Tailwind color name
                    'Water'     => 'blue',
                    'Pest'      => 'red',
                    'Stake'     => 'stone',
                    'Inspect'   => 'yellow',
                    default     => 'default'
                };


                $roles = ($performer?->roles?->isNotEmpty())
                    ? ' (' . $performer->roles->pluck('name')->join(', ') . ')'
                    : '';


                $userName = trim(
                    $performer?->id . ' - ' . $performer?->first_name . ' ' . $performer?->last_name
                ) . $roles;

                // Description for Day view
                $descriptionParts = [];
                $descriptionParts[] = 'Type: ' . $type->name;
                if ($performer) $descriptionParts[] = 'Performed by: ' . $userName;
                if (!is_null($m->quantity)) {
                    $descriptionParts[] = 'Quantity: ' . rtrim(rtrim(number_format($m->quantity, 2, '.', ''), '0'), '.');
                }
                if (!is_null($m->cost)) {
                    $descriptionParts[] = 'Cost: €' . number_format($m->cost, 2, '.', '');
                }
                if ($m->notes) $descriptionParts[] = $m->notes;

                $description = implode("\n", array_filter($descriptionParts));


                return [
                    'id'                 => 'maint_' . $m->event_id,
                    'event_type'         => 'maintenance',
                    'user_id'            => $m->performed_by,
                    'user_name'          => $userName,
                    'tree_id'            => $m->tree_id,
                    'tree_label'         => $treeLabel,
                    'tree'               => $tree,
                    'maintenance_type_id'   => $m->type_id,
                    'maintenance_type_name' => $type->name,
                    'start'              => optional($m->performed_at)->toIso8601String(),
                    'title'              => $type->name . ' – ' . $treeLabel,
                    'description'        => $description !== '' ? $description : null,
                    'color'              => $color,
                    'meta'               => [
                        'quantity'  => $m->quantity,
                        'cost'      => $m->cost,
                        'notes'     => $m->notes,
                        'tree'      => $tree,
                        'species'   => $species,
                        'type'      => $type,
                        'neighborhood' => $tree->neighborhood ?? null,
                    ],
                ];
            })
            ->filter()
            ->values()
            ->collect();



        // return collect($plantings)->merge(collect($maintenance))->toArray();
        return $plantings
            ->merge($maintenance)
            ->sortBy('start')
            ->values()
            ->all();
    }
}
