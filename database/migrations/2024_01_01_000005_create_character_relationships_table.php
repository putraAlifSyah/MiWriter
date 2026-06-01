<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_one_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('character_two_id')->constrained('characters')->cascadeOnDelete();
            $table->string('relationship_type', 50);
            $table->timestamp('created_at')->nullable();

            $table->unique(['character_one_id', 'character_two_id'], 'unique_relationship');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_relationships');
    }
};
