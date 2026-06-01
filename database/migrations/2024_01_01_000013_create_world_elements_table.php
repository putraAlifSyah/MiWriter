<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('category', 50);
            $table->text('description')->nullable();
            $table->text('rules_laws')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_elements');
    }
};
