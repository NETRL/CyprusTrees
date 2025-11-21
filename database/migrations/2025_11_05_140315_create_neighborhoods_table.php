<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('city', 120)->nullable();
            $table->string('district', 120)->nullable();
            $table->string('geom_ref', 255)->unique()->nullable();
            $table->timestamps();
        });

        // Add PostGIS geometry column (WGS84, MultiPolygon)
        DB::statement("
            ALTER TABLE neighborhoods
            ADD COLUMN geom geometry(MULTIPOLYGON, 4326)
        ");

        // Spatial index for fast contains/intersects queries
        DB::statement("
            CREATE INDEX neighborhoods_geom_gix
            ON neighborhoods
            USING GIST (geom)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
    }
};
