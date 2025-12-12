<?php

namespace App\Http\Controllers;

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
        $plantingsQuery = PlantingEvent::with([
            'tree.species',
            'tree.neighborhood',
            'campaign',
            'planter',
        ])
            ->whereBetween('planted_at', [$start, $end]);

        if (!$isAdmin && $userId) {
            $plantingsQuery->where('planted_by', $userId);
        }

        $plantings = $plantingsQuery->get()
            ->map(function (PlantingEvent $p) {
                if (!$p->tree || !$p->tree->species) {
                    // malformed or orphaned, skip
                    return null;
                }

                $tree      = $p->tree;
                $species   = $tree->species;
                $campaign  = $p->campaign;
                $planter   = $p->planter;

                $treeLabel = $species->common_name
                    . ' (' . $species->latin_name . ')';

                $roles = ($planter?->roles?->isNotEmpty())
                    ? ' (' . $planter->roles->pluck('name')->join(', ') . ')'
                    : '';

                $userName = trim(
                    $planter?->id . ' - ' . $planter?->first_name . ' ' . $planter?->last_name
                ) . $roles;


                // Human-readable description for Day view
                $descriptionParts = [];
                if ($campaign) $descriptionParts[] = 'Campaign: ' . $campaign->name;
                if ($planter)  $descriptionParts[] = 'Planted by: ' . $userName;
                if ($p->method) $descriptionParts[] = 'Method: ' . $p->method;
                if ($p->notes)  $descriptionParts[] = $p->notes;

                $description = implode("\n", array_filter($descriptionParts));


                return [
                    'id'          => 'planting_' . $p->planting_id,
                    'event_type'  => 'planting',
                    'user_id'     => $p->planted_by,
                    'user_name'   => $userName,
                    'tree_id'     => $p->tree_id,
                    'tree_label'  => $treeLabel,
                    'tree'        => $tree,
                    'campaign_id' => $p->campaign_id,
                    'campaign'    => $campaign,
                    'title'       => 'Planting – ' . $treeLabel,
                    'start'       => optional($p->planted_at)->toIso8601String(),
                    'description' => $description !== '' ? $description : null,
                    'color'       => 'emerald', // Tailwind color name
                    'meta'        => [
                        'method'  => $p->method,
                        'notes'   => $p->notes,
                        'tree'    => $tree,
                        'species' => $species,
                        'neighborhood' => $tree->neighborhood ?? null,
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
