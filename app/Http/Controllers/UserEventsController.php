<?php

namespace App\Http\Controllers;

use App\Enums\PlantingEventStatus;
use App\Models\MaintenanceEvent;
use App\Models\PlantingEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserEventsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Use app timezone (Europe/Athens)
        $now = now();

        $events = collect()
            ->merge($this->getPlantingEventsForUser($user->id, $now))
            ->merge($this->getMaintenanceEventsForUser($user->id, $now))
            ->sortBy(function ($e) {
                return $e['start'] ? Carbon::parse($e['start'])->timestamp : PHP_INT_MAX;
            })
            ->values()
            ->all();

        return Inertia::render('User/Events/Index', [
            'events' => $events,
        ]);
    }

    private function getPlantingEventsForUser(int $userId, Carbon $now)
    {
        // Include scheduled/in_progress/overdue and also show completed in "Completed" tab
        $items = PlantingEvent::query()
            ->with([
                'campaign:id,name,sponsor',
                'neighborhood:id,name',
            ])
            ->withCount(['eventTrees'])
            ->where('assigned_to', $userId)
            ->orderByRaw("COALESCE(started_at, created_at) ASC")
            ->get();

        return $items->map(function (PlantingEvent $e) use ($now) {
            $status = (string) $e->status;

            // Treat started_at as "planned start" when scheduled,
            // and actual start when in_progress.
            $start = $e->started_at
                ? Carbon::parse($e->started_at)
                : Carbon::parse($e->created_at);

            $end = $e->completed_at ? Carbon::parse($e->completed_at) : null;

            $treesCount = (int) ($e->event_trees_count ?? 0);
            $target = $e->target_tree_count;

            $campaignLabel = $e->campaign
                ? ($e->campaign->name . ($e->campaign->sponsor ? " ({$e->campaign->sponsor})" : ''))
                : null;

            $titleParts = ['Planting'];
            if ($e->neighborhood?->name) $titleParts[] = $e->neighborhood->name;
            $title = implode(' – ', $titleParts);

            $whenText = $this->formatRange($start, $end);

            $locationText = method_exists($e, 'getLocationAttribute')
                ? $e->getLocationAttribute()
                : ($e->neighborhood?->name ?? null);

            $progressText = is_null($target)
                ? "{$treesCount} trees"
                : "{$treesCount}/{$target} trees";

            $tab = $this->tabForPlanting($status, $start, $end, $now);
            $actions = $this->actionsForPlanting($status);

            return [
                'key' => 'planting:' . $e->planting_id,

                'type' => 'planting',
                'typeLabel' => 'Planting',

                'title' => $title,
                'status' => $status,
                'statusLabel' => $this->labelForPlantingStatus($status),

                'start' => $start?->toIso8601String(),
                'end' => $end?->toIso8601String(),

                'whenText' => $whenText,
                'locationText' => $locationText,

                'lat' => $e->lat,
                'lon' => $e->lon,

                'progressText' => $progressText,

                'isToday' => $start->isSameDay($now),
                'isOverdue' => $tab === 'overdue',

                'tab' => $tab,

                'mapUrl' => route('/', [
                    'mode' => 'planting',
                    'event_id' => $e->planting_id,
                    'lat' => $e->lat,
                    'lon' => $e->lon,
                ]),

                // link back to office table / details
                'detailsUrl' => route('plantingEvents.index', [
                    'search' => $e->planting_id,
                ]),

                'actions' => $actions,

                'startUrl' => route('plantingEvents.start', $e->planting_id),
                'completeUrl' => route('plantingEvents.complete', $e->planting_id,),

                'meta' => [
                    'planting_id' => $e->planting_id,
                    'campaign' => $campaignLabel,
                    'trees_count' => $treesCount,
                    'target_tree_count' => $target,
                ],
            ];
        });
    }

    private function getMaintenanceEventsForUser(int $userId, Carbon $now)
    {
        // Maintenance schema has performed_at; we treat it as the event datetime.
        $items = MaintenanceEvent::query()
            ->with([
                'tree:id,species_id,neighborhood_id,lat,lon,address',
                'tree.species:id,latin_name,common_name',
                'tree.neighborhood:id,name',
                'type:type_id,name',
            ])
            ->where('performed_by', $userId)
            ->orderBy('performed_at')
            ->get();

        return $items->map(function (MaintenanceEvent $m) use ($now) {
            $performedAt = $m->performed_at ? Carbon::parse($m->performed_at) : null;

            $species = $m->tree?->species;
            $treeLabel = $species
                ? (($species->common_name ?? '-') . ' (' . ($species->latin_name ?? '-') . ')')
                : 'Tree #' . $m->tree_id;

            $typeName = $m->type?->name ?? 'Maintenance';

            $title = $typeName . ' – ' . $treeLabel;

            $whenText = $performedAt ? $this->formatPoint($performedAt) : null;

            $locationText = $m->tree?->neighborhood?->name
                ?? $m->tree?->address
                ?? null;

            // Infer maintenance "status"
            $status = $performedAt
                ? ($performedAt->lessThanOrEqualTo($now) ? 'completed' : 'scheduled')
                : 'scheduled';

            $tab = $this->tabForMaintenance($status, $performedAt, $now);

            return [
                'key' => 'maintenance:' . $m->event_id,

                'type' => 'maintenance',
                'typeLabel' => 'Maintenance',

                'title' => $title,
                'status' => $status,
                'statusLabel' => $status === 'completed' ? 'Completed' : 'Scheduled',

                'start' => $performedAt?->toIso8601String(),
                'end' => null,

                'whenText' => $whenText,
                'locationText' => $locationText,

                'lat' => $m->tree?->lat,
                'lon' => $m->tree?->lon,

                'progressText' => null,

                'isToday' => $performedAt ? $performedAt->isSameDay($now) : false,
                'isOverdue' => $tab === 'overdue',

                'tab' => $tab,

                'mapUrl' => route('/', [
                    'mode' => 'maintenance',
                    'event_id' => $m->event_id,
                    'tree_id' => $m->tree_id,
                    'lat' => $m->tree?->lat,
                    'lon' => $m->tree?->lon,
                ]),

                'detailsUrl' => route('maintenanceEvents.index', [
                    'search' => $m->event_id,
                ]),

                'actions' => [],

                'meta' => [
                    'event_id' => $m->event_id,
                    'tree_id' => $m->tree_id,
                    'type' => $typeName,
                ],
            ];
        });
    }

    private function tabForPlanting(string $status, Carbon $start, ?Carbon $end, Carbon $now): string
    {
        if ($status === PlantingEventStatus::IN_PROGRESS->value) return 'in_progress';

        if (in_array($status, [PlantingEventStatus::COMPLETED->value, PlantingEventStatus::CANCELLED->value], true)) {
            return 'completed';
        }

        // scheduled/draft: overdue if start is in the past (and not completed)
        if (in_array($status, [PlantingEventStatus::SCHEDULED->value, PlantingEventStatus::DRAFT->value], true)) {
            if ($start->isSameDay($now)) return 'today';
            if ($start->lessThan($now->copy()->startOfDay())) return 'overdue';
            return 'upcoming';
        }

        // fallback
        return $start->isSameDay($now) ? 'today' : ($start->lessThan($now) ? 'overdue' : 'upcoming');
    }

    private function actionsForPlanting(string $status): array
    {
        return match ($status) {
            PlantingEventStatus::SCHEDULED->value => ['start'],
            PlantingEventStatus::IN_PROGRESS->value => ['complete'],
            default => [],
        };
    }

    private function labelForPlantingStatus(string $status): string
    {
        return match ($status) {
            PlantingEventStatus::DRAFT->value => 'Draft',
            PlantingEventStatus::SCHEDULED->value => 'Scheduled',
            PlantingEventStatus::IN_PROGRESS->value => 'In Progress',
            PlantingEventStatus::COMPLETED->value => 'Completed',
            PlantingEventStatus::CANCELLED->value => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function tabForMaintenance(string $status, ?Carbon $at, Carbon $now): string
    {
        if ($status === 'completed') return 'completed';

        if (!$at) return 'upcoming';

        if ($at->isSameDay($now)) return 'today';

        if ($at->lessThan($now->copy()->startOfDay())) return 'overdue';

        return 'upcoming';
    }

    private function formatRange(Carbon $start, ?Carbon $end): string
    {
        if (!$end) return $this->formatPoint($start);

        // Same day: "Jan 29, 10:00–12:00"
        if ($start->isSameDay($end)) {
            return $start->format('M j, H:i') . '–' . $end->format('H:i');
        }

        // Multi-day: "Jan 29, 10:00 → Jan 30, 12:00"
        return $start->format('M j, H:i') . ' → ' . $end->format('M j, H:i');
    }

    private function formatPoint(Carbon $at): string
    {
        return $at->format('d/m/Y, H:i');
    }
}
