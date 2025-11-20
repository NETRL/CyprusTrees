<?php

namespace Database\Seeders;

use App\Models\Photo;
use App\Models\Tree;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PhotosTableSeeder extends Seeder
{
    public function run(): void
    {
        $trees = Tree::all();

        if ($trees->isEmpty()) {
            $this->command?->warn('No trees found — seed TreesTableSeeder first.');
            return;
        }

        $urlBase = 'https://loremflickr.com/600/400/tree';

        foreach ($trees as $tree) {
            $count = rand(1, 4);
            for ($i = 0; $i < $count; $i++) {
                Photo::create([
                    'tree_id'      => $tree->id,
                    'url'          => $urlBase . '?random=' . rand(1, 10000),
                    'caption'      => fake()->sentence(),
                    'captured_at'  => now()->subDays(rand(1, 500)),
                    'source'       => 'seed-placeholder',
                    'path'         => null,
                    'status'       => 'complete',
                    'error_message' => null,
                ]);
            }
        }
    }
}
