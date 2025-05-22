<?php

namespace App\Filament\Resources\BookTransactionResource\Pages;

use App\Filament\Resources\BookTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookTransaction extends EditRecord
{
    protected static string $resource = BookTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
