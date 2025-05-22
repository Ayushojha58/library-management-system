<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookTransaction;

class BookTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Create some borrowed books
        BookTransaction::factory()
            ->count(30)
            ->create();

        // Create some returned books
        BookTransaction::factory()
            ->count(20)
            ->returned()
            ->create();

        // Create some overdue books
        BookTransaction::factory()
            ->count(10)
            ->overdue()
            ->create();
    }
}
