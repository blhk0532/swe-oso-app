<?php

namespace App\Filament\Resources\MerinfoDatas\Tables;

use App\Models\MerinfoData;
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

class MerinfoDatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('personnamn')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('alder')
                    ->label('Age')
                    ->sortable(),

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

                TextColumn::make('gatuadress')
                    ->label('Address')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('postnummer')
                    ->label('Zip')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('postort')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('telefon_preview')
                    ->label('Phone')
                    ->getStateUsing(fn ($record) => $record->telefon_preview)
                    ->copyable()
                    ->copyMessage('Full phone data copied')
                    ->copyableState(fn ($record) => is_array($record->telefon) ? implode(' | ', $record->telefon) : (string) ($record->telefon ?? ''))
                    ->color(function ($record): string {
                        $arr = is_array($record->telefon) ? $record->telefon : (is_string($record->telefon) ? [$record->telefon] : []);
                        $hasReal = collect($arr)->filter(fn ($n) => $n && ! str_contains($n, 'Lägg till telefonnummer'))->isNotEmpty();

                        return $hasReal ? 'success' : 'gray';
                    })
                    ->tooltip(fn ($record) => is_array($record->telefon) ? implode(' | ', $record->telefon) : (string) ($record->telefon ?? '')),

                IconColumn::make('is_hus')
                    ->label('Hus')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

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
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All records')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                TernaryFilter::make('is_telefon')
                    ->label('Has Phone')
                    ->placeholder('All records')
                    ->trueLabel('With phone')
                    ->falseLabel('Without phone'),

                TernaryFilter::make('is_ratsit')
                    ->label('Ratsit')
                    ->placeholder('All records')
                    ->trueLabel('In Ratsit')
                    ->falseLabel('Not in Ratsit'),

                SelectFilter::make('kon')
                    ->label('Sex')
                    ->options([
                        'Man' => 'Man',
                        'Kvinna' => 'Kvinna',
                    ]),

                SelectFilter::make('postort')
                    ->label('City')
                    ->options(
                        fn (): array => MerinfoData::query()
                            ->whereNotNull('postort')
                            ->distinct()
                            ->pluck('postort', 'postort')
                            ->toArray()
                    )
                    ->searchable(),

                Filter::make('postnummer')
                    ->form([
                        TextInput::make('postnummer')
                            ->label('Zip Code'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['postnummer'] ?? null,
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
            ->paginated([10, 25, 50, 100, 200, 500, 1000])
            ->striped()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistFiltersInSession()
            ->persistSortInSession();
    }
}
