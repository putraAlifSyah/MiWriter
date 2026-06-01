<?php

namespace Database\Factories;

use App\Enums\BookStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'genre' => fake()->randomElement(['Fantasy', 'Sci-Fi', 'Romance', 'Mystery', 'Thriller']),
            'synopsis' => fake()->paragraph(),
            'status' => fake()->randomElement(BookStatus::cases()),
        ];
    }
}
