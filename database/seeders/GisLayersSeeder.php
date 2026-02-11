<?php

namespace Database\Seeders;

use App\Models\GisLayer;
use Illuminate\Database\Seeder;

class GisLayersSeeder extends Seeder
{
    public function run(): void
    {
        $layers = [
            [
                'key' => 'irrigation_lines',
                'name' => 'Irrigation Lines',
                'display_name' => 'Irrigation Lines',
                'category' => 'irrigation',
                'source' => 'municipality',
                'default_import_mode' => 'append',
                'is_editable' => false,
                'is_active' => true,
                'metadata' => [
                    'color' => "#1E88E5",
                ],
            ],
            [
                'key' => 'irrigation_points',
                'name' => 'Irrigation Points',
                'display_name' => 'Irrigation Points (Valves/Hydrants)',
                'category' => 'irrigation',
                'source' => 'municipality',
                'default_import_mode' => 'append',
                'is_editable' => false,
                'is_active' => true,
                'metadata' => [
                    'color' => "#1565C0",
                ],
            ],
            [
                'key' => 'greenspaces',
                'name' => 'Green Spaces',
                'display_name' => 'Green Spaces',
                'category' => 'greenspace',
                'source' => 'municipality',
                'default_import_mode' => 'replace',
                'is_editable' => false,
                'is_active' => true,
                'metadata' => [
                    'color' => "#66BB6A",
                ],
            ],
            [
                'key' => 'playgrounds',
                'name' => 'Playgrounds',
                'display_name' => 'Playgrounds',
                'category' => 'playground',
                'source' => 'municipality',
                'default_import_mode' => 'replace',
                'is_editable' => false,
                'is_active' => true,
                'metadata' => [
                    'color' => "#FFA726",
                ],
            ],
            [
                'key' => 'parks',
                'name' => 'Parks',
                'display_name' => 'Parks',
                'category' => 'greenspace',
                'source' => 'municipality',
                'default_import_mode' => 'replace',
                'is_editable' => false,
                'is_active' => true,
                'metadata' => [
                    'color' => "#2E7D32",
                ],
            ],
            [
                'key' => 'walking_paths',
                'name' => 'Walking Paths',
                'display_name' => 'Walking Paths',
                'category' => 'mobility',
                'source' => 'municipality',
                'default_import_mode' => 'append',
                'is_editable' => false,
                'is_active' => true,
                'metadata' => [
                    'color' => "#8D6E63",
                ],
            ],
        ];

        foreach ($layers as $layer) {
            GisLayer::query()->updateOrCreate(
                ['key' => $layer['key']],
                $layer
            );
        }
    }
}
