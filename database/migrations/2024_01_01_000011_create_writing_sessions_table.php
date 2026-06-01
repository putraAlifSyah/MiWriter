<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->integer('words_written')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->date('session_date');
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_sessions');
    }
};
