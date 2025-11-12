<?php

namespace App\Filament\Resources\PostNummerQues\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PostNummerQuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_nummer')
                    ->label('Post Nr')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap']),

                TextColumn::make('post_ort')
                    ->label('Post Ort')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'whitespace-nowrap'])
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('TE')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('house')
                    ->label('HS')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('count')
                    ->label('CN')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('total_count')
                    ->label('TT')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('bolag')
                    ->label('AB')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('foretag')
                    ->label('FÖ')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('personer')
                    ->label('PE')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('platser')
                    ->label('PL')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('computed_progress')
                    ->label('%')
                    ->html()
                    ->getStateUsing(function ($record): string {
                        $total = (int) ($record->total_count ?? 0);
                        $done = (int) ($record->count ?? 0);
                        $p = $total > 0 ? (int) min(100, floor(($done / $total) * 100)) : (int) ($record->progress ?? 0);

                        return "<div class='w-28'>"
                            . "<div class='h-1.5 w-full bg-gray-200/60 dark:bg-gray-700/50 rounded'>"
                            . "<div class='h-1.5 bg-blue-500 rounded' style='width: {$p}%;'></div>"
                            . '</div>'
                            . "<div class='mt-1 text-xs text-gray-500 dark:text-gray-400'>{$p}%</div>"
                            . '</div>';
                    })
                    ->alignCenter()
                    ->sortable()
                    ->grow(false),

                IconColumn::make('is_active')
                    ->label('OK')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'gray',
                        'running' => 'warning',
                        'complete' => 'success',
                        'empty' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '—')
                    ->sortable()
                    ->grow(false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'running' => 'Running',
                        'complete' => 'Complete',
                        'empty' => 'Empty',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('bulkResetValues')
                        ->label('Reset All Data')
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reset All Data')
                        ->modalDescription('This will reset count, phone, house, bolag, foretag, personer, platser, progress, and processing data for selected records.')
                        ->modalSubmitActionLabel('Reset')
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update([
                                'count' => 0,
                                'phone' => 0,
                                'house' => 0,
                                'bolag' => 0,
                                'foretag' => 0,
                                'personer' => 0,
                                'platser' => 0,
                                'progress' => 0,
                                'last_processed_page' => 0,
                                'processed_count' => 0,
                                'status' => 'pending',
                            ]));

                            Notification::make()
                                ->title('Data Reset')
                                ->body('Selected records have been reset.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('post_nummer', 'asc');
    }
}
