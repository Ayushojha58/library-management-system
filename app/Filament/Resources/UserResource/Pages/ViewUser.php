<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\BookTransaction;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Actions as InfolistActions;
use Filament\Infolists\Components\Actions\Action;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('borrowed');
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('borrowed_count'),
                RepeatableEntry::make('borrowed')
                    ->label('Borrowed Books')
                    ->columnSpanFull()
                    ->grid(3)
                    ->schema([
                        TextEntry::make('book.title')->label('Title'),
                        TextEntry::make('book.author')->label('Author'),
                        InfolistActions::make([
                            Action::make('return')
                                ->label('Return Book')
                                ->icon('heroicon-o-arrow-left')
                                ->color('success')
                                ->action(function (BookTransaction $record) {
                                    $record->returned_date = now();
                                    $record->status = 'returned';
                                    $record->save();

                                    $record->book->available_copies++;
                                    $record->book->save();
                                })
                                ->visible(function (BookTransaction $record) {
                                    return $record->status === 'borrowed';
                                }),
                        ]),


                    ])
            ]);
    }
}
