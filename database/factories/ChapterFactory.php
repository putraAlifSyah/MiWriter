<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChapterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'title' => 'Chapter ' . fake()->numberBetween(1, 50),
            'content_html' => '<p>' . fake()->paragraphs(3, true) . '</p>',
            'word_count' => fake()->numberBetween(100, 5000),
            'order_number' => fake()->numberBetween(1, 50),
        ];
    }
}
