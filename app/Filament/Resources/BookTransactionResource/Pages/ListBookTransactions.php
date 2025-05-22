<?php

namespace App\Filament\Resources\BookTransactionResource\Pages;

use App\Filament\Resources\BookTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookTransactions extends ListRecords
{
    protected static string $resource = BookTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
