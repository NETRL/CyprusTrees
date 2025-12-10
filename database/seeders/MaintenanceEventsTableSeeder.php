<?php

namespace Database\Seeders;

use App\Models\MaintenanceEvent;
use App\Models\MaintenanceType;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MaintenanceEventsTableSeeder extends Seeder
{
    public function run(): void
    {
        $trees            = Tree::all();
        $maintenanceTypes = MaintenanceType::all();
        $users            = User::all();

        if ($trees->isEmpty() || $maintenanceTypes->isEmpty()) {
            $this->command?->warn('Trees or MaintenanceTypes missing – seed TreesTableSeeder and MaintenanceTypesTableSeeder first.');
            return;
        }

        foreach ($trees as $tree) {
            // 0–3 events per tree
            $eventCount = rand(0, 3);

            for ($i = 0; $i < $eventCount; $i++) {
                $type = $maintenanceTypes->random();

                // Performed date after planting
                $baseDate = $tree->planted_at
                    ? Carbon::parse($tree->planted_at)
                    : now()->subYears(5);

                $performedAt = $baseDate
                    ->copy()
                    ->addDays(rand(30, 1500))
                    ->setTime(rand(7, 15), rand(0, 59));

                MaintenanceEvent::create([
                    'tree_id'      => $tree->id,
                    // uses model primary key regardless of whether it's id/type_id
                    'type_id'      => $type->getKey(),
                    'performed_by' => $users->isNotEmpty() && rand(1, 100) > 30
                        ? $users->random()->id
                        : null,
                    'performed_at' => $performedAt,

                    // Quantity aligned with type name
                    'quantity'     => match ($type->name) {
                        'Water'   => rand(50, 200),  // litres
                        'Prune'   => rand(1, 5),     // number of major cuts
                        'Pest'    => rand(1, 5),     // treatments
                        'Stake'   => rand(1, 3),     // stakes / supports
                        'Inspect' => 1,              // one inspection
                        'Other'   => rand(1, 3),
                        default   => rand(1, 3),
                    },

                    // Cost aligned with type name
                    'cost'         => match ($type->name) {
                        'Water'   => rand(5, 25),    // cheap, operational
                        'Prune'   => rand(30, 150),  // labour intensive
                        'Pest'    => rand(20, 120),  // materials + labour
                        'Stake'   => rand(15, 80),   // materials
                        'Inspect' => rand(10, 60),   // staff time
                        'Other'   => rand(5, 50),
                        default   => rand(5, 50),
                    },

                    'notes'        => "Maintenance ({$type->name}) in {$tree->neighborhood?->name} (Nicosia district).",
                ]);
            }
        }
    }
}
