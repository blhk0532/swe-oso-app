<?php

namespace App\Filament\Resources\HittaSes\Tables;

use App\Models\HittaSe;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HittaSesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Name
                TextColumn::make('personnamn')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Age
                TextColumn::make('alder')
                    ->label('Age')
                    ->sortable(),

                // Sex
                TextColumn::make('kon')
                    ->label('Sex')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Man' => 'info',
                        'Kvinna' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                // Address
                TextColumn::make('gatuadress')
                    ->label('Address')
                    ->searchable()
                    ->sortable(),

                // Zip
                TextColumn::make('postnummer')
                    ->label('Zip')
                    ->searchable()
                    ->sortable(),

                // City
                TextColumn::make('postort')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                // Phone (array of numbers)
                TextColumn::make('telefon')
                    ->label('Phone')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(' | ', $state) : (string) $state)
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone number(s) copied')
                    ->color(function ($state): string {
                        if (is_array($state)) {
                            return count($state) ? 'success' : 'gray';
                        }

                        return ($state === 'Lägg till telefonnummer' || empty($state)) ? 'gray' : 'success';
                    }),

                // Ratsit
                IconColumn::make('is_ratsit')
                    ->label('Ratsit')
                    ->boolean()
                    ->sortable(),

                // Active
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                // Additional toggleable columns
                IconColumn::make('is_telefon')
                    ->label('Has Phone')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('karta')
                    ->label('Map')
                    ->url(fn ($record) => $record->karta)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('link')
                    ->label('Profile')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Active status filter
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All records')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                // Has phone filter
                TernaryFilter::make('is_telefon')
                    ->label('Has Phone')
                    ->placeholder('All records')
                    ->trueLabel('With phone')
                    ->falseLabel('Without phone'),

                // Ratsit filter
                TernaryFilter::make('is_ratsit')
                    ->label('Ratsit')
                    ->placeholder('All records')
                    ->trueLabel('In Ratsit')
                    ->falseLabel('Not in Ratsit'),

                // Sex filter
                SelectFilter::make('kon')
                    ->label('Sex')
                    ->options([
                        'Man' => 'Man',
                        'Kvinna' => 'Kvinna',
                    ]),

                // City filter
                SelectFilter::make('postort')
                    ->label('City')
                    ->options(
                        fn (): array => HittaSe::query()
                            ->whereNotNull('postort')
                            ->distinct()
                            ->pluck('postort', 'postort')
                            ->toArray()
                    )
                    ->searchable(),

                // Zip code filter
                Filter::make('postnummer')
                    ->form([
                        TextInput::make('postnummer')
                            ->label('Zip Code'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['postnummer'],
                                fn (Builder $query, $postnummer): Builder => $query->where('postnummer', 'like', "%{$postnummer}%")
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->paginated([10, 25, 50, 100, 200])
            ->striped()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistFiltersInSession()
            ->persistSortInSession();
    }
}
