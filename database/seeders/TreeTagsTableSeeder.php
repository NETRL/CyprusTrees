<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Tree;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class TreeTagsTableSeeder extends Seeder
{
    public function run(): void
    {
        $trees = Tree::all();
        $tags  = Tag::pluck('id')->all();

        if ($trees->isEmpty() || empty($tags)) {
            $this->command?->warn('Trees or Tags table empty. Run TreesTableSeeder and TagsTableSeeder first.');
            return;
        }

        foreach ($trees as $tree) {
            // Attach 1–3 random tags per tree
            $tree->tags()->syncWithoutDetaching(
                Arr::random($tags, rand(1, min(3, count($tags))))
            );
        }
    }
}
