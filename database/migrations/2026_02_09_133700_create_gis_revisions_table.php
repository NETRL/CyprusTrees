<?php

use App\Models\GisLayer;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gis_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(GisLayer::class, 'layer_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'imported_by')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('revision_no')->nullable();     // optional monotonic number per layer
            $table->string('label')->nullable();                    // "Survey Feb 2026", "Contractor v2", etc.
            $table->string('import_mode', 16)->default('replace');  // replace|append (what THIS revision did)

            $table->string('status', 24)->default('queued');    // queued|processing|failed|completed|archived

            $table->string('original_name')->nullable();
            $table->string('original_ext', 12)->nullable(); // geojson|json
            $table->string('stored_path');                  // where the raw file lives

            $table->unsignedInteger('features_imported')->default(0);
            $table->jsonb('feature_counts')->nullable(); // {"POINT":123,"LINESTRING":45,"POLYGON":6}

            // Optional spatial summary for UI + quick zoom-to-layer
            $table->jsonb('bbox')->nullable(); // [minLon,minLat,maxLon,maxLat]
            // $table->point('centroid', 4326)->nullable();

            $table->text('error')->nullable();

            $table->timestamp('activated_at')->nullable(); // when it became active (if it did)
            $table->timestamp('archived_at')->nullable();  // when archived
            $table->boolean('is_included')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['layer_id', 'revision_no']);
        });
        DB::statement('ALTER TABLE gis_revisions ADD COLUMN centroid geometry(Point,4326)');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gis_layers');
    }
};
