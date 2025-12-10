<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\PlantingEvent;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlantingEventsTableSeeder extends Seeder
{
    public function run(): void
    {
        $trees     = Tree::all();
        $campaigns = Campaign::all();
        $users     = User::all();

        if ($trees->isEmpty()) {
            $this->command?->warn('No trees found – seed TreesTableSeeder first.');
            return;
        }

        if ($campaigns->isEmpty()) {
            $this->command?->warn('No campaigns found – seed CampaignsTableSeeder first.');
            return;
        }

        foreach ($trees as $tree) {
            // Only create planting events for ~40% of trees
            if (rand(1, 100) > 40) {
                continue;
            }

            $campaign = $campaigns->random();

            // Use tree’s planted_at if available, otherwise random past date
            $plantedAt = $tree->planted_at
                ? Carbon::parse($tree->planted_at)
                : now()->subYears(rand(1, 10))->subDays(rand(0, 365));

            PlantingEvent::firstOrCreate(
                ['tree_id' => $tree->id],
                [
                    'campaign_id' => rand(1, 100) > 20 ? $campaign->id : null, // some trees not tied to campaign
                    'planted_by'  => $users->isNotEmpty() ? $users->random()->id : null,
                    'planted_at'  => $plantedAt,
                    'method'      => collect(['manual planting', 'mechanical auger', 'school volunteer event'])->random(),
                    'notes'       => "Tree planted in {$tree->neighborhood?->name} as part of a greening effort in Nicosia.",
                ]
            );
        }
    }
}
