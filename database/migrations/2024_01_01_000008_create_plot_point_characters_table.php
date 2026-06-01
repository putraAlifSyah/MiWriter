<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plot_point_characters', function (Blueprint $table) {
            $table->foreignId('plot_point_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();

            $table->primary(['plot_point_id', 'character_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plot_point_characters');
    }
};
