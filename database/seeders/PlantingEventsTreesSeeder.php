<?php

namespace Database\Seeders;

use App\Enums\PlantingEventStatus;
use App\Models\Photo;
use App\Models\PlantingEvent;
use App\Models\PlantingEventTree;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

            $isActual = in_array($status, [PlantingEventStatus::IN_PROGRESS, PlantingEventStatus::COMPLETED], true);

            // Candidate selection rules
            $treeQuery = Tree::query();

            if (!empty($event->neighborhood_id)) {
                $treeQuery->where('neighborhood_id', $event->neighborhood_id);
            }

            // Prefer “real” points for actual planting
            if ($isActual) {
                $treeQuery->whereNotNull('lat')->whereNotNull('lon');
            }

            /**
             * IMPORTANT CHANGE (unique(tree_id)):
             * Exclude ANY tree already linked to ANY planting event.
             * This must be global, not filtered by planting_id.
             *
             * Requires Tree::plantingRecord() hasOne(PlantingEventTree...)
             */
            $treeQuery->whereDoesntHave('plantingRecord');

            $available = (clone $treeQuery)->count();
            if ($available <= 0) {
                continue;
            }

            $count = min($desiredCount, $available);

            $trees = $treeQuery->inRandomOrder()->limit($count)->get();

            foreach ($trees as $tree) {
                // Extra safety: should never happen due to whereDoesntHave + unique(tree_id)
                $alreadyLinked = PlantingEventTree::query()
                    ->where('tree_id', $tree->getKey())
                    ->exists();

                if ($alreadyLinked) {
                    continue;
                }

                $plantedBy = $isActual ? $users->random() : null;
                $plantedAt = $isActual ? $this->computePlantedAtForActual($status, $event) : null;

                PlantingEventTree::query()->create([
                    'planting_id'     => $event->getKey(),      // uses PlantingEvent PK (planting_id)
                    'tree_id'         => $tree->getKey(),
                    'planted_by'      => $plantedBy?->getKey(),
                    'planted_at'      => $plantedAt,
                    'planting_method' => $methods->random(),
                    'notes'           => $this->makeItemNotes($status),
                ]);

                // Backfill trees.planted_at if you keep it there for completed plantings
                if ($status === PlantingEventStatus::COMPLETED && $plantedAt && empty($tree->planted_at)) {
                    $tree->forceFill(['planted_at' => Carbon::parse($plantedAt)->toDateString()])->save();
                }

                /**
                 * OPTIONAL (recommended): seed at least 1 photo for actual planting events,
                 * and attach it to the planting event via planting_events_photos.
                 *
                 * This assumes PlantingEvent::photos() belongsToMany is set up.
                 */
                if ($isActual) {
                    $this->seedPlantingPhotos($event, $tree, $plantedAt);
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

    private function seedPlantingPhotos(PlantingEvent $event, Tree $tree, ?Carbon $capturedAt): void
    {
        // If you want “required”, make it always >= 1. Keep small to avoid heavy seed.
        $photoCount = rand(1, 3);

        // If your UI expects processing pipeline, you can set status='ready' and provide a URL.
        // This avoids having to store files & run jobs during seeding.
        $photos = [];

        for ($i = 0; $i < $photoCount; $i++) {
            $photos[] = Photo::query()->create([
                'tree_id'     => $tree->getKey(),
                'caption'     => 'Planting documentation',
                'url'         => $this->fakeImageUrl($tree->getKey(), $event->getKey(), $i),
                'captured_at' => $capturedAt ?? now(),
                'source'      => 'upload',
                'path'        => null,
                'status'      => 'ready',
            ]);
        }

        // Attach to event via pivot (planting_events_photos)
        // Requires PlantingEvent::photos() relationship
        $event->photos()->syncWithoutDetaching(collect($photos)->pluck('id')->all());
    }

    private function fakeImageUrl(int $treeId, int $plantingId, int $index): string
    {
        // Stable-ish placeholder. Replace with your own CDN/static assets if you prefer.
        $seed = Str::slug("tree{$treeId}-planting{$plantingId}-{$index}");
        return "https://picsum.photos/seed/{$seed}/800/600";
    }
}
