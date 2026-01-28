<?php

use App\Models\Campaign;
use App\Models\Neighborhood;
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
        Schema::create('planting_events', function (Blueprint $table) {
            $table->id('planting_id');

            $table->foreignIdFor(Campaign::class)->nullable()->constrained();
            $table->foreignIdFor(Neighborhood::class)->nullable()->constrained();
            $table->foreignIdFor(User::class, 'assigned_to')->nullable()->constrained();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lon', 9, 6)->nullable();

            $table->integer('target_tree_count')->nullable();

            // draft | scheduled | in_progress | completed | canceled
            $table->string('status', 20)->default('draft')->index();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_events');
    }
};
