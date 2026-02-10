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
                    'style' => [
                        'type' => 'line',
                        'paint' => [
                            'line-width' => 2,
                            'line-opacity' => 0.9,
                        ],
                        'layout' => [
                            'line-cap' => 'round',
                            'line-join' => 'round',
                        ],
                    ],
                    'popup' => [
                        'titleField' => 'name',
                        'fields' => ['type', 'diameter', 'material', 'status'],
                    ],
                    'notes' => 'Imported from field surveys; append new surveys over time.',
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
                    'style' => [
                        'type' => 'circle',
                        'paint' => [
                            'circle-radius' => 5,
                            'circle-opacity' => 0.9,
                        ],
                    ],
                    'popup' => [
                        'titleField' => 'name',
                        'fields' => ['type', 'status'],
                    ],
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
                    'style' => [
                        'type' => 'fill',
                        'paint' => [
                            'fill-opacity' => 0.35,
                        ],
                    ],
                    'popup' => [
                        'titleField' => 'name',
                        'fields' => ['type', 'area_m2'],
                    ],
                    'notes' => 'Usually maintained as a snapshot (replace) when official boundaries are updated.',
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
                    'style' => [
                        'type' => 'fill',
                        'paint' => [
                            'fill-opacity' => 0.35,
                        ],
                    ],
                    'popup' => [
                        'titleField' => 'name',
                        'fields' => ['type', 'surface', 'status'],
                    ],
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
                    'style' => [
                        'type' => 'fill',
                        'paint' => [
                            'fill-opacity' => 0.25,
                        ],
                    ],
                    'popup' => [
                        'titleField' => 'name',
                        'fields' => ['type', 'area_m2'],
                    ],
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
                    'style' => [
                        'type' => 'line',
                        'paint' => [
                            'line-width' => 2,
                            'line-opacity' => 0.8,
                        ],
                        'layout' => [
                            'line-cap' => 'round',
                            'line-join' => 'round',
                        ],
                    ],
                    'popup' => [
                        'titleField' => 'name',
                        'fields' => ['type', 'surface', 'status'],
                    ],
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
