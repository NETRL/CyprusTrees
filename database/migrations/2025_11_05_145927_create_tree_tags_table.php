<?php

use App\Models\Tag;
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
        Schema::create('tree_tags', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Tree::class)->constrained();
            $table->foreignIdFor(Tag::class)->constrained();

            $table->timestamps();

            $table->unique(['tree_id', 'tag_id'], 'uq_tree_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_tags');
    }
};
