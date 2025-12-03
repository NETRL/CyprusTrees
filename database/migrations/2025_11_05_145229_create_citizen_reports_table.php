<?php

use App\Models\Photo;
use App\Models\ReportType;
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
        Schema::create('citizen_reports', function (Blueprint $table) {
            $table->id('report_id');

            $table->foreignIdFor(ReportType::class)->constrained();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained();
            $table->foreignIdFor(Tree::class)->nullable()->constrained();
            $table->foreignIdFor(Photo::class)->nullable()->constrained();

            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lon', 9, 6)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->index(['lat', 'lon'], 'idx_reports_latlon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizen_reports');
    }
};
