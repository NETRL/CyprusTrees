<?php

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
        Schema::create('health_assessments', function (Blueprint $table) {
            $table->id('assessment_id');

            $table->foreignIdFor(Tree::class)->constrained();
            $table->foreignIdFor(User::class, 'assessed_by')->nullable()->constrained();

            $table->timestamp('assessed_at')->nullable();
            $table->string('health_status', 20)->nullable();
            $table->text('pests_diseases')->nullable();
            $table->decimal('risk_score', 3, 2)->nullable();
            $table->text('actions_recommended')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_assesments');
    }
};
