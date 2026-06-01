<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('name', 200);
            $table->enum('type', ['city', 'building', 'landscape', 'realm', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->text('atmosphere')->nullable();
            $table->text('notable_features')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->tinyInteger('depth')->unsigned()->default(0);
            $table->timestamps();

            $table->index(['book_id', 'type']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
