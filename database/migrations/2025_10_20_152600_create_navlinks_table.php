<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navlinks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->string('permissions')->nullable();
            $table->string('route_name')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('external')->default(0);

            $table->foreign('parent_id')->references('id')->on('navlinks');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navlinks');
    }
};
