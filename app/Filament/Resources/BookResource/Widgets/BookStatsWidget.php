<?php

namespace App\Filament\Resources\BookResource\Widgets;

use App\Models\Book;
use App\Models\BookTransaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total books', Book::count()),
            Stat::make('Total Users', User::count()),
            Stat::make('Total Borrowed', BookTransaction::where('status', '!=', 'returned')->count()),
        ];
    }
}
