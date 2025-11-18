<?php

use App\Models\Tree;
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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Tree::class)->constrained();

            $table->string('url', 255)->nullable();
            $table->string('caption', 255)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->string('source', 60)->nullable();
            $table->string('path')->nullable();
            $table->string('status')->default('processing');
            $table->string('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
