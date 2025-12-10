<?php

namespace Database\Seeders;

use App\Enums\HealthStatus;
use App\Models\HealthAssessment;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HealthAssessmentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $trees = Tree::all();
        $users = User::all();

        if ($trees->isEmpty()) {
            $this->command?->warn('No trees found – seed TreesTableSeeder first.');
            return;
        }

        // Assess ~30% of trees
        foreach ($trees as $tree) {
            if (rand(1, 100) > 30) {
                continue;
            }

            $assessedAt = $tree->last_inspected_at
                ? Carbon::parse($tree->last_inspected_at)
                : now()->subDays(rand(10, 400));

            // Select a random enum case, and use the enum case itself
            /** @var HealthStatus $healthStatus */
            $healthStatus = HealthStatus::cases()[array_rand(HealthStatus::cases())];

            // Use the enum cases directly in the match statement instead of raw strings
            // Revised logic for risk_score to align with $table->decimal('risk_score', 3, 2)

            $riskScore = match ($healthStatus) {
                HealthStatus::EXCELLENT => rand(0, 100) / 100,      // Range: 0.00–1.00 (Max 1.00)
                HealthStatus::GOOD      => rand(50, 250) / 100,     // Range: 0.50–2.50 (Max 2.50)
                HealthStatus::FAIR      => rand(150, 500) / 100,    // Range: 1.50–5.00 (Max 5.00)
                HealthStatus::POOR      => rand(400, 800) / 100,    // Range: 4.00–8.00 (Max 8.00)
                HealthStatus::CRITICAL  => rand(700, 999) / 100,    // Range: 7.00–9.99 (Max 9.99)
                HealthStatus::DEAD      => 9.99,                    
            };

            $pestsDiseases = match ($healthStatus) {
                HealthStatus::EXCELLENT => null,
                HealthStatus::GOOD      => 'Minor pruning wounds; no significant pest presence.',
                HealthStatus::FAIR      => 'Early signs of fungal infection on trunk; few dead branches.',
                HealthStatus::POOR      => 'Extensive decay and structural defects; potential risk to pedestrians.',
                HealthStatus::CRITICAL  => 'Severe root rot and significant structural failure imminent.', // New
                HealthStatus::DEAD      => 'Tree is dead, fully dried out, and potentially brittle.',      // New
            };

            $actions = match ($healthStatus) {
                HealthStatus::EXCELLENT => 'Continue regular monitoring every 2–3 years.',
                HealthStatus::GOOD      => 'Light pruning and check irrigation schedule, especially for summer months in Nicosia.',
                HealthStatus::FAIR      => 'Schedule detailed arborist inspection and corrective pruning within 3 months.',
                HealthStatus::POOR      => 'Urgent risk assessment; consider partial removal or replacement.',
                HealthStatus::CRITICAL  => 'Immediate fencing of area and emergency removal to mitigate risk.', // New
                HealthStatus::DEAD      => 'Schedule removal and stump grinding ASAP.',                          // New
            };

            HealthAssessment::create([
                'tree_id'               => $tree->id,
                'assessed_by'           => $users->isNotEmpty() ? $users->random()->id : null,
                'assessed_at'           => $assessedAt,
                // When passing the enum case to a model, Laravel handles the casting 
                // to its string value automatically if the model is set up correctly.
                'health_status'         => $healthStatus,
                'pests_diseases'        => $pestsDiseases,
                'risk_score'            => $riskScore,
                'actions_recommended'   => $actions,
            ]);
        }
    }
}
