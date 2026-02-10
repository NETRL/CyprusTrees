<?php

use App\Models\GisRevision;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gis_layers', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();    // stable identifier: irrigation_lines, playgrounds, etc.
            $table->string('name');             // internal name
            $table->string('display_name');     // UI name

            $table->string('category', 48)->nullable(); // irrigation|greenspace|playground|roads|etc (optional)
            $table->string('source')->nullable();

            // How this layer should behave by default when new data arrives
            $table->string('default_import_mode', 16)->default('replace'); // replace|append

            $table->boolean('is_editable')->default(false);
            $table->boolean('is_active')->default(true);

            $table->jsonb('metadata')->nullable(); // styles, attribution, notes, schema hints, etc.

            $table->timestamps();
            $table->softDeletes(); // optional
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gis_layers');
    }
};
