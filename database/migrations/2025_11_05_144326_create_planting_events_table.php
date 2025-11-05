<?php

use App\Models\Campaign;
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

            $table->foreignIdFor(Tree::class)->constrained();
            $table->foreignIdFor(Campaign::class)->nullable()->constrained();
            $table->foreignIdFor(User::class, 'planted_by')->nullable()->constrained();

            $table->timestamp('planted_at')->nullable();
            $table->string('method', 60)->nullable();
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
