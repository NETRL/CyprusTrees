<?php

use App\Models\GisImport;
use App\Models\GisLayer;
use App\Models\GisRevision;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gis_features', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(GisLayer::class, 'layer_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(GisRevision::class, 'revision_id')->constrained()->cascadeOnDelete();

            $table->string('geom_type', 24)->index(); // POINT|LINESTRING|POLYGON|MULTI*
            $table->jsonb('properties')->nullable();

            // stable feature id from source if present
            $table->string('source_feature_id', 128)->nullable();

            $table->timestamps();

            $table->index(['layer_id', 'revision_id']);
        });

        DB::statement("ALTER TABLE gis_features ADD COLUMN geom geometry(Geometry, 4326)");
        DB::statement("CREATE INDEX gis_features_geom_gist ON gis_features USING GIST (geom)");
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_features');
    }
};
