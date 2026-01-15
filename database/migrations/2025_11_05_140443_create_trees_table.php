<?php

use App\Models\Neighborhood;
use App\Models\Species;
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
        Schema::create('trees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Species::class)->constrained();
            $table->foreignIdFor(Neighborhood::class)->nullable()->constrained();

            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lon', 9, 6)->nullable();
            $table->string('address', 255)->nullable();

            $table->date('planted_at')->nullable();
            $table->string('status', 20)->nullable();
            $table->string('health_status', 20)->nullable();
            $table->string('sex', 10)->nullable();

            $table->decimal('height_m', 5, 2)->nullable();
            $table->decimal('dbh_cm', 5, 1)->nullable();
            $table->decimal('canopy_diameter_m', 5, 2)->nullable();

            $table->timestamp('last_inspected_at')->nullable();
            $table->string('owner_type', 20)->nullable();
            $table->string('source', 60)->nullable();

            $table->timestamps();

            // map index
            $table->index(['lat', 'lon'], 'idx_trees_latlon');

            // $table->softDeletes();
        });

        DB::statement("
            ALTER TABLE trees
            ADD COLUMN geom geometry(Point, 4326)
        ");

        DB::statement("
            CREATE INDEX trees_geom_gix
            ON trees
            USING GIST (geom)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trees');
    }
};
