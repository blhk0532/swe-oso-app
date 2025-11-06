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

                // Phone (array/string) — show first number; ellipsis if it's 12+ chars
                TextColumn::make('telefon')
                    ->label('Phone')
                    ->formatStateUsing(function ($state) {
                        // Coerce into an array from array | JSON string | delimited string
                        $list = [];

                        if (is_array($state)) {
                            $list = $state;
                        } elseif (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                $list = $decoded;
                            } else {
                                $parts = preg_split('/[\s,;\n]+/', $state) ?: [];
                                $list = $parts;
                            }
                        }

                        // Normalize, filter invalids, dedupe
                        $numbers = array_values(array_unique(array_filter(array_map(function ($n) {
                            if (! is_string($n)) {
                                return '';
                            }
                            $n = trim($n);
                            // Remove obvious noise like lone commas or empties
                            if ($n === '' || $n === ',') {
                                return '';
                            }
                            $digits = preg_replace('/[^0-9]/', '', $n);

                            return strlen($digits) >= 8 ? $n : '';
                        }, $list))));

                        $first = $numbers[0] ?? '';
                        if ($first === '') {
                            return '';
                        }

                        // If the first number is long, show an ellipsis after 12 chars
                        return strlen($first) >= 12 ? substr($first, 0, 12) . '…' : $first;
                    })
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone number(s) copied')
                    ->copyableState(function ($record) {
                        $state = $record->telefon;
                        $list = [];

                        if (is_array($state)) {
                            $list = $state;
                        } elseif (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                $list = $decoded;
                            } else {
                                $parts = preg_split('/[\s,;\n]+/', $state) ?: [];
                                $list = $parts;
                            }
                        }

                        $numbers = array_values(array_unique(array_filter(array_map(function ($n) {
                            if (! is_string($n)) {
                                return '';
                            }
                            $n = trim($n);
                            if ($n === '' || $n === ',') {
                                return '';
                            }
                            $digits = preg_replace('/[^0-9]/', '', $n);

                            return strlen($digits) >= 8 ? $n : '';
                        }, $list))));

                        // Copy the full first number
                        return $numbers[0] ?? '';
                    })
                    ->color(function ($state): string {
                        $list = [];
                        if (is_array($state)) {
                            $list = $state;
                        } elseif (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                $list = $decoded;
                            } else {
                                $parts = preg_split('/[\s,;\n]+/', $state) ?: [];
                                $list = $parts;
                            }
                        }

                        $numbers = array_values(array_unique(array_filter(array_map(function ($n) {
                            if (! is_string($n)) {
                                return '';
                            }
                            $n = trim($n);
                            if ($n === '' || $n === ',') {
                                return '';
                            }
                            $digits = preg_replace('/[^0-9]/', '', $n);

                            return strlen($digits) >= 6 ? $n : '';
                        }, $list))));

                        return count($numbers) ? 'success' : 'gray';
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
