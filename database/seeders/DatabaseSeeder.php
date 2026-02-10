<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(NavlinkSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(UserSeeder::class);
        // Core lookups & users
        $this->call([

            TagsTableSeeder::class,
            SpeciesTableSeeder::class,
            NeighborhoodsTableSeeder::class,
            ReportTypeTableSeeder::class,
            MaintenanceTypesTableSeeder::class,
            CampaignsTableSeeder::class,
        ]);

        // Trees and relations
        $this->call([
            TreesTableSeeder::class,
            TreeTagsTableSeeder::class,
            PhotosTableSeeder::class,
        ]);

        // Domain-specific events & reports
        $this->call([
            PlantingEventsTableSeeder::class,
            PlantingEventsTreesSeeder::class,
            CitizenReportsTableSeeder::class,
            MaintenanceEventsTableSeeder::class,
            HealthAssessmentsTableSeeder::class,
        ]);
        
        $this->call(GisLayersSeeder::class);
    }
}
