<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('genre', 100)->nullable();
            $table->text('synopsis')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed', 'archived'])->default('draft');
            $table->string('cover_image_path', 500)->nullable();
            $table->string('cover_thumbnail_path', 500)->nullable();
            $table->unsignedInteger('target_word_count')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
