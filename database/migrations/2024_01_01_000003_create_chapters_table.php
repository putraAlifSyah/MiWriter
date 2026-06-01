<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->longText('content_html')->nullable();
            $table->json('content_delta')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('order_number');
            $table->timestamps();

            $table->index(['book_id', 'order_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
