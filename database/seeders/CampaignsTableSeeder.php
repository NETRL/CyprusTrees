<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CampaignsTableSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            [
                'name'       => 'Green Nicosia – School Tree Planting',
                'sponsor'    => 'Municipality of Nicosia & Ministry of Education',
                'start_date' => Carbon::create(2023, 11, 1),
                'end_date'   => Carbon::create(2024, 3, 31),
                'notes'      => 'Tree planting with primary schools in Kaimakli, Pallouriotissa and Engomi.',
            ],
            [
                'name'       => 'Strovolos Riverside Planting',
                'sponsor'    => 'Strovolos Municipality & Local NGOs',
                'start_date' => Carbon::create(2022, 2, 15),
                'end_date'   => Carbon::create(2022, 4, 30),
                'notes'      => 'Planting along Pedieos river path, focusing on native species like olives and carobs.',
            ],
            [
                'name'       => 'Aglandjia Urban Forest Pockets',
                'sponsor'    => 'Aglandjia Municipality & CYENS',
                'start_date' => Carbon::create(2024, 10, 1),
                'end_date'   => Carbon::create(2025, 4, 30),
                'notes'      => 'Pocket-forest style plantings near universities and residential areas.',
            ],
            [
                'name'       => 'Within the Walls – Historic Trees Care',
                'sponsor'    => 'Municipality of Nicosia & Cultural Services',
                'start_date' => Carbon::create(2021, 5, 1),
                'end_date'   => Carbon::create(2021, 10, 31),
                'notes'      => 'Maintenance and planting of trees in the old town within the Venetian walls.',
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::firstOrCreate(
                ['name' => $campaign['name']],
                $campaign
            );
        }
    }
}
