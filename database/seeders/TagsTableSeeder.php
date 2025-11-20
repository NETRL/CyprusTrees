<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Young tree'],
            ['name' => 'Mature tree'],
            ['name' => 'Old tree'],
            ['name' => 'Needs watering'],
            ['name' => 'Recently planted'],
            ['name' => 'Damaged branch'],
            ['name' => 'Trunk damage'],
            ['name' => 'Pest / disease suspect'],
            ['name' => 'Leaning tree'],
            ['name' => 'Near power lines'],
            ['name' => 'Sidewalk damage'],
            ['name' => 'Heritage tree'],
            ['name' => 'Memorial tree'],
            ['name' => 'Playground area'],
            ['name' => 'School area'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['name' => $tag['name']], $tag);
        }
    }
}
