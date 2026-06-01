<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('role', ['protagonist', 'antagonist', 'supporting', 'minor']);
            $table->text('physical_description')->nullable();
            $table->text('personality_traits')->nullable();
            $table->text('backstory')->nullable();
            $table->text('motivations')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->timestamps();

            $table->index(['book_id', 'name']);
            $table->index(['book_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
