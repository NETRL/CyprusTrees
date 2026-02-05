<?php

use App\Models\Photo;
use App\Models\PlantingEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planting_events_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(PlantingEvent::class, 'planting_id')->constrained('planting_events', 'planting_id')->cascadeOnDelete();
            $table->foreignIdFor(Photo::class)->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['planting_id', 'photo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_events_photos');
    }
};
