<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_element_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->timestamp('created_at')->nullable();

            $table->unique(['book_id', 'name'], 'unique_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_element_categories');
    }
};
