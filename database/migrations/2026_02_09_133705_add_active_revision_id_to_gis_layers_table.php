<?php

use App\Models\GisRevision;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gis_layers', function (Blueprint $table) {
            // Active revision pointer (for fast map rendering)
            $table->foreignIdFor(GisRevision::class, 'active_revision_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gis_layers', function (Blueprint $table) {
            $table->dropForeignIdFor(GisRevision::class, 'active_revision_id');
        });
    }
};
