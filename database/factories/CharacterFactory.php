<?php

namespace Database\Factories;

use App\Enums\CharacterRole;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'name' => fake()->name(),
            'role' => fake()->randomElement(CharacterRole::cases()),
            'physical_description' => fake()->paragraph(),
            'personality_traits' => fake()->paragraph(),
            'backstory' => fake()->paragraphs(2, true),
        ];
    }
}
