<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_element_references', function (Blueprint $table) {
            $table->foreignId('source_id')->constrained('world_elements')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('world_elements')->cascadeOnDelete();

            $table->primary(['source_id', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_element_references');
    }
};
