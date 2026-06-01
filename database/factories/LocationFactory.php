<?php

namespace Database\Factories;

use App\Enums\LocationType;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'name' => fake()->city(),
            'type' => fake()->randomElement(LocationType::cases()),
            'description' => fake()->paragraph(),
            'depth' => 0,
        ];
    }
}
