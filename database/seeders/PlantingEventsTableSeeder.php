<?php

namespace Database\Seeders;

use App\Enums\PlantingEventStatus;
use App\Models\Campaign;
use App\Models\Neighborhood;
use App\Models\PlantingEvent;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlantingEventsTableSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns     = Campaign::query()->get();
        $neighborhoods = Neighborhood::query()->get();
        $users         = User::query()->get();
        $trees         = Tree::query()->get();

        if ($neighborhoods->isEmpty()) {
            $this->command?->warn('No neighborhoods found – seed NeighborhoodsTableSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command?->warn('No users found – seed UsersTableSeeder first.');
            return;
        }

        if ($trees->isEmpty()) {
            $this->command?->warn('No trees found – seed TreesTableSeeder first.');
            return;
        }

        $eventCount = max(8, min(40, (int) floor($trees->count() / 30)));

        $statusPool = collect([
            PlantingEventStatus::DRAFT,
            PlantingEventStatus::SCHEDULED,
            PlantingEventStatus::SCHEDULED,
            PlantingEventStatus::IN_PROGRESS,
            PlantingEventStatus::COMPLETED,
            PlantingEventStatus::COMPLETED,
            PlantingEventStatus::COMPLETED,
            PlantingEventStatus::CANCELLED,
        ]);

        for ($i = 0; $i < $eventCount; $i++) {
            $status = $statusPool->random();

            $createdBy  = $users->random();
            $assignedTo = (rand(1, 100) <= 80) ? $users->random() : null;

            // Prefer neighborhood for statuses that need real assignments
            $preferNeighborhood = in_array($status, [PlantingEventStatus::IN_PROGRESS, PlantingEventStatus::COMPLETED], true);
            $neighborhood = ($preferNeighborhood || rand(1, 100) <= 85) ? $neighborhoods->random() : null;

            $campaign = ($campaigns->isNotEmpty() && rand(1, 100) <= 70) ? $campaigns->random() : null;

            $target = rand(5, 45);

            $centerLat = null;
            $centerLon = null;

            if ($neighborhood) {
                $candidate = Tree::query()
                    ->where('neighborhood_id', $neighborhood->getKey())
                    ->whereNotNull('lat')
                    ->whereNotNull('lon')
                    ->inRandomOrder()
                    ->first();

                if ($candidate) {
                    $centerLat = $candidate->lat;
                    $centerLon = $candidate->lon;
                }
            }

            [$startedAt, $completedAt] = $this->makeTimeline($status);

            PlantingEvent::query()->create([
                'campaign_id'        => $campaign?->getKey(),
                'neighborhood_id'    => $neighborhood?->getKey(),
                'assigned_to'        => $assignedTo?->getKey(),
                'created_by'         => $createdBy->getKey(),
                'started_at'         => $startedAt,
                'completed_at'       => $completedAt,
                'lat'                => $centerLat,
                'lon'                => $centerLon,
                'target_tree_count'  => $target,
                'status'             => $status->value,
                'notes'              => $this->makeNotes($status, $neighborhood, $campaign),
            ]);
        }
    }

    private function makeTimeline(PlantingEventStatus $status): array
    {
        $base = now()->subDays(rand(0, 300));

        return match ($status) {
            PlantingEventStatus::DRAFT => [null, null],

            PlantingEventStatus::SCHEDULED => [null, null],

            PlantingEventStatus::IN_PROGRESS => [
                Carbon::parse($base)->addHours(rand(0, 48)),
                null,
            ],

            PlantingEventStatus::COMPLETED => (function () use ($base) {
                $start = Carbon::parse($base)->addHours(rand(0, 48));
                $end   = (clone $start)->addHours(rand(2, 10));
                return [$start, $end];
            })(),

            PlantingEventStatus::CANCELLED => [null, null],
        };
    }

    private function makeNotes(PlantingEventStatus $status, ?Neighborhood $n, ?Campaign $c): string
    {
        $nName = $n?->name ?? 'Nicosia';
        $cName = $c?->name;

        return match ($status) {
            PlantingEventStatus::DRAFT =>
                "Draft planting event prepared for {$nName}.",

            PlantingEventStatus::SCHEDULED =>
                $cName
                    ? "Scheduled planting event in {$nName} as part of campaign '{$cName}'."
                    : "Scheduled planting event in {$nName}.",

            PlantingEventStatus::IN_PROGRESS =>
                "Field crew currently planting in {$nName}.",

            PlantingEventStatus::COMPLETED =>
                $cName
                    ? "Completed planting in {$nName} under campaign '{$cName}'."
                    : "Completed planting in {$nName}.",

            PlantingEventStatus::CANCELLED =>
                "Cancelled planting event in {$nName} (logistics/weather constraints).",
        };
    }
}
