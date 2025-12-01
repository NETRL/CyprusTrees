<?php

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
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('latin_name', 120)->nullable();
            $table->string('common_name', 120)->nullable();
            $table->string('family', 120)->nullable();
            $table->string('origin', 20)->nullable();               // enum
            $table->tinyInteger('opals_score')->nullable();         // 0-10
            $table->string('drought_tolerance', 20)->nullable();    // enum
            $table->string('canopy_class', 10)->nullable();         // enum
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};
