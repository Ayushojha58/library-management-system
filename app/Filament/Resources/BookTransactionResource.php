<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookTransactionResource\Pages\CreateBookTransaction;
use App\Filament\Resources\BookTransactionResource\Pages\EditBookTransaction;
use App\Filament\Resources\BookTransactionResource\Pages\ListBookTransactions;
use App\Models\BookTransaction;
use Filament\Actions\Action as HeaderAction;
use Filament\Actions\CreateAction as HeaderCreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class BookTransactionResource extends Resource
{
    protected static ?string $model = BookTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('book_id')
                            ->relationship('book', 'title')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('borrowed_date')
                            ->required()
                            ->default(now())
                            ->minDate(now()),
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->default(now()->addDays(14))
                            ->minDate(now()),
                        Forms\Components\DatePicker::make('returned_date')
                            ->nullable()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'borrowed' => 'Borrowed',
                                'returned' => 'Returned',
                                'overdue' => 'Overdue',
                            ])
                            ->disabled()
                            ->default('borrowed'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.title')
                    ->searchable()
                    ->label('Book'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('User'),
                Tables\Columns\TextColumn::make('borrowed_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('returned_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'returned',
                        'danger' => 'overdue',
                        'warning' => 'borrowed',
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_overdue')
                    ->query(function (Builder $query) {
                        return $query->where('status', 'overdue');
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'borrowed' => 'Borrowed',
                        'returned' => 'Returned',
                        'overdue' => 'Overdue',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('return')
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
                Tables\Actions\Action::make('mark_overdue')
                    ->label('Mark Overdue')
                    ->icon('heroicon-o-clock')
                    ->color('danger')
                    ->action(function (BookTransaction $record) {
                        $record->status = 'overdue';
                        $record->save();
                    })
                    ->visible(function (BookTransaction $record) {
                        return $record->status === 'borrowed' && now()->gt($record->due_date);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('return_selected')
                        ->label('Return Selected Books')
                        ->icon('heroicon-o-arrow-left')
                        ->color('success')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->returned_date = now();
                                $record->status = 'returned';
                                $record->save();

                                $record->book->available_copies++;
                                $record->book->save();
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getHeaderActions(): array
    {
        return [
            HeaderCreateAction::make()
                ->label('Borrow Book')
                ->icon('heroicon-o-book-open'),
            HeaderAction::make('return_book')
                ->label('Return Book')
                ->icon('heroicon-o-arrow-left')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('book_id')
                        ->label('Book')
                        ->relationship('book', 'title')
                        ->required()
                        ->searchable()
                        ->query(function (Builder $query) {
                            return $query->whereHas('transactions', function (Builder $query) {
                                return $query->whereNull('returned_date');
                            });
                        }),
                    Forms\Components\Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->required()
                        ->searchable()
                        ->query(function (Builder $query) {
                            return $query->whereHas('bookTransactions', function (Builder $query) {
                                return $query->whereNull('returned_date');
                            });
                        }),
                ])
                ->action(function (array $data) {
                    $transaction = BookTransaction::where('book_id', $data['book_id'])
                        ->where('user_id', $data['user_id'])
                        ->whereNull('returned_date')
                        ->first();

                    if (! $transaction) {
                        throw new \Exception('No active transaction found for this book and user');
                    }

                    $transaction->returned_date = now();
                    $transaction->status = 'returned';
                    $transaction->save();

                    $transaction->book->available_copies++;
                    $transaction->book->save();
                })
                ->modalWidth('lg')
                ->modalHeading('Return Book')
                ->modalDescription('Select the book and user to return the book'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookTransactions::route('/'),
            'create' => CreateBookTransaction::route('/create'),
            'edit' => EditBookTransaction::route('/{record}/edit'),
        ];
    }
}
