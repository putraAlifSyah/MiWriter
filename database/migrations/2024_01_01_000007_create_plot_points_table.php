<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plot_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->enum('act', ['beginning', 'middle', 'end'])->default('beginning');
            $table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
            $table->string('color_label', 20)->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->index(['book_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plot_points');
    }
};
