<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'total_copies' => $this->faker->numberBetween(1, 10),
            'available_copies' => function (array $attributes) {
                return $attributes['total_copies'];
            },
            'category_id' => fn () => \App\Models\Category::inRandomOrder()->first()->id,
            'rack_id' => fn () => \App\Models\Rack::inRandomOrder()->first()->id,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Book $book) {
            //
        })->afterCreating(function (Book $book) {
            $book->updateAvailability();
        });
    }
}
