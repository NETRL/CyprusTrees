<?php

namespace Database\Seeders;

use App\Enums\PlantingEventStatus;
use App\Models\PlantingEvent;
use App\Models\PlantingEventTree;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlantingEventsTreesSeeder extends Seeder
{
    public function run(): void
    {
        $events = PlantingEvent::query()->get();
        $users  = User::query()->get();

        if ($events->isEmpty()) {
            $this->command?->warn('No planting events found – seed PlantingEventsTableSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command?->warn('No users found – seed UsersTableSeeder first.');
            return;
        }

        $methods = collect([
            'manual planting',
            'mechanical auger',
            'volunteer planting day',
            'contractor planting',
        ]);

        foreach ($events as $event) {
            $status = PlantingEventStatus::tryFrom((string) $event->status) ?? PlantingEventStatus::DRAFT;

            $target = (int) ($event->target_tree_count ?? rand(5, 30));

            // How many items we want to create for this status
            $desiredCount = match ($status) {
                PlantingEventStatus::DRAFT       => rand(0, 2),
                PlantingEventStatus::SCHEDULED   => rand(0, (int) floor($target / 3)),
                PlantingEventStatus::IN_PROGRESS => rand((int) floor($target / 4), (int) floor($target / 2)),
                PlantingEventStatus::COMPLETED   => rand((int) floor($target * 0.7), $target),
                PlantingEventStatus::CANCELLED   => rand(0, 2),
            };

            if ($desiredCount <= 0) {
                continue;
            }

            // Candidate selection rules:
            $treeQuery = Tree::query();

            if (!empty($event->neighborhood_id)) {
                $treeQuery->where('neighborhood_id', $event->neighborhood_id);
            }

            // For "actual planting", prefer trees that are not already planted
            if (in_array($status, [PlantingEventStatus::IN_PROGRESS, PlantingEventStatus::COMPLETED], true)) {
                $treeQuery->whereNull('planted_at');

                // Usually you want real map points
                $treeQuery->whereNotNull('lat')->whereNotNull('lon');
            }

            // Avoid trees already linked to this event (unique safeguard)
            $treeQuery->whereDoesntHave('plantingEventTrees', function ($q) use ($event) {
                $q->where('planting_id', $event->getKey());
            });

            // If not enough candidates exist, shrink count
            $available = (clone $treeQuery)->count();
            if ($available <= 0) {
                continue;
            }

            $count = min($desiredCount, $available);

            $trees = $treeQuery->inRandomOrder()->limit($count)->get();

            foreach ($trees as $tree) {
                // extra safety for unique(planting_id, tree_id)
                $exists = PlantingEventTree::query()
                    ->where('planting_id', $event->getKey())
                    ->where('tree_id', $tree->getKey())
                    ->exists();

                if ($exists) continue;

                $isActual = in_array($status, [PlantingEventStatus::IN_PROGRESS, PlantingEventStatus::COMPLETED], true);

                $plantedBy = $isActual ? $users->random() : null;
                $plantedAt = $isActual ? $this->computePlantedAtForActual($status, $event) : null;

                PlantingEventTree::query()->create([
                    'planting_id'     => $event->getKey(),
                    'tree_id'         => $tree->getKey(),
                    'planted_by'      => $plantedBy?->getKey(),
                    'planted_at'      => $plantedAt,
                    'planting_method' => $methods->random(),
                    'notes'           => $this->makeItemNotes($status),
                ]);

                // Backfill the tree.planted_at for actual planted trees
                if ($status === PlantingEventStatus::COMPLETED && $plantedAt && empty($tree->planted_at)) {
                    $tree->forceFill(['planted_at' => Carbon::parse($plantedAt)->toDateString()])->save();
                }
            }
        }
    }

    private function computePlantedAtForActual(PlantingEventStatus $status, PlantingEvent $event): Carbon
    {
        if ($status === PlantingEventStatus::IN_PROGRESS) {
            return now()->subHours(rand(1, 6));
        }

        // COMPLETED: force within event window
        $start = $event->started_at ? Carbon::parse($event->started_at) : now()->subDays(rand(1, 120));
        $end   = $event->completed_at ? Carbon::parse($event->completed_at) : (clone $start)->addHours(rand(2, 10));

        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        $seconds = max(60, $end->diffInSeconds($start));
        return (clone $start)->addSeconds(rand(0, $seconds));
    }

    private function makeItemNotes(PlantingEventStatus $status): string
    {
        return match ($status) {
            PlantingEventStatus::DRAFT =>
                "Planned tree entry for draft event.",

            PlantingEventStatus::SCHEDULED =>
                "Reserved/planned for upcoming event.",

            PlantingEventStatus::IN_PROGRESS =>
                "Planted on-site (in progress).",

            PlantingEventStatus::COMPLETED =>
                "Tree planted successfully during event.",

            PlantingEventStatus::CANCELLED =>
                "Event cancelled; no planting performed.",
        };
    }
}
