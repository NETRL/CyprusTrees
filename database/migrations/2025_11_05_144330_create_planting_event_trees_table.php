<?php

use App\Models\PlantingEvent;
use App\Models\Tree;
use App\Models\User;
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
        Schema::create('planting_events_trees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PlantingEvent::class, 'planting_id')->constrained('planting_events', 'planting_id')->cascadeOnDelete();
            $table->foreignIdFor(Tree::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'planted_by')->nullable()->constrained();

            $table->timestamp('planted_at')->nullable();
            $table->string('planting_method', 60)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['planting_id', 'tree_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_events_trees');
    }
};
