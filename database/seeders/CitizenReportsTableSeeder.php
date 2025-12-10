<?php

namespace Database\Seeders;

use App\Enums\ReportStatus;
use App\Models\CitizenReport;
use App\Models\Photo;
use App\Models\ReportType;
use App\Models\Tree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CitizenReportsTableSeeder extends Seeder
{
    public function run(): void
    {
        $trees       = Tree::with('neighborhood')->get();
        $reportTypes = ReportType::all()->keyBy('name');
        $users       = User::all();
        $photos      = Photo::all();

        if ($trees->isEmpty() || $reportTypes->isEmpty()) {
            $this->command?->warn('Trees or ReportTypes missing – seed TreesTableSeeder and ReportTypesTableSeeder first.');
            return;
        }

        // Create ~120 reports around Nicosia
        for ($i = 0; $i < 120; $i++) {
            $tree        = $trees->random();
            // Select a random report type model
            $reportType  = $reportTypes->random(); 

            // Use tree’s coordinates with a tiny random offset
            $lat = $tree->lat + (rand(-5, 5) / 10000); // ~tens of meters
            $lon = $tree->lon + (rand(-5, 5) / 10000);

            $createdAt = now()->subDays(rand(0, 365))->setTime(rand(7, 22), rand(0, 59));

            $user = $users->isNotEmpty() ? $users->random() : null;

            // Optional photo from the same tree
            $photo = $photos->where('tree_id', $tree->id)->random() ?? null;

            $neighbourhoodName = $tree->neighborhood?->name ?? 'Nicosia';

            // Use the ReportType model directly in the match expression
            $description = match ($reportType->name) {
                // Using the specific names defined in ReportTypeTableSeeder
                'Irrigation' =>
                    "Ο κάτοικος αναφέρει ότι το δέντρο στην περιοχή {$neighbourhoodName} φαίνεται πολύ ξερό. Παρακαλούμε έλεγχο ποτίσματος.",
                'Damage' =>
                    "Σπασμένο κλαδί πάνω από το πεζοδρόμιο στην {$neighbourhoodName}, κοντά σε στάση λεωφορείου.",
                'Disease' =>
                    "Παράξενα σημάδια στα φύλλα και πιθανή παρουσία εντόμων. Έλεγχος υγείας δέντρου στην {$neighbourhoodName}.",
                'Other' =>
                    "Ύποπτη έντονη κλάδευση δέντρου σε οδό της {$neighbourhoodName}. Παρακαλώ έλεγχο.",
                'Suggestion' =>
                    "Αίτημα για νέο δέντρο σε κενό δενδροστοιχίας στην {$neighbourhoodName}.",
                default =>
                    "Αναφορά πολίτη για δέντρο στην περιοχή {$neighbourhoodName}.",
            };

            CitizenReport::create([
                'report_type_id' => $reportType->id,
                'created_by'     => $user?->id,
                'tree_id'        => $tree->id,
                'photo_id'       => $photo?->id,
                'lat'            => $lat,
                'lon'            => $lon,
                'description'    => $description,
                'status'         => ReportStatus::cases()[array_rand(ReportStatus::cases())],
                'created_at'     => $createdAt,
                'resolved_at'    => rand(1, 100) > 60
                    ? (clone $createdAt)->addDays(rand(1, 60))
                    : null,
            ]);
        }
    }
}