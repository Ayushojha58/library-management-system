<?php

namespace Database\Factories;

use App\Models\BookTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookTransactionFactory extends Factory
{
    protected $model = BookTransaction::class;

    public function definition(): array
    {
        // Get a random book that has available copies
        $book = \App\Models\Book::where('available_copies', '>', 0)
            ->inRandomOrder()
            ->first();

        return [
            'book_id' => $book->id,
            'user_id' => fn () => \App\Models\User::inRandomOrder()->first()->id,
            'borrowed_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_date' => null,
            'status' => 'borrowed',
        ];
    }

    public function returned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'returned_date' => now(),
                'status' => 'returned',
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'due_date' => now()->subDays(7),
            ];
        });
    }
}
