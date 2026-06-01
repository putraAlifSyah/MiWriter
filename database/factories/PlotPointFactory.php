<?php

namespace Database\Factories;

use App\Enums\PlotAct;
use App\Enums\PlotStatus;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlotPointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'act' => fake()->randomElement(PlotAct::cases()),
            'status' => fake()->randomElement(PlotStatus::cases()),
            'position' => fake()->numberBetween(1, 20),
        ];
    }
}
