<?php

namespace App\Filament\Resources\PostNummers\Tables;

use App\Jobs\CheckHittaTotals;
use App\Jobs\ProcessPostNummer;
use Filament\Actions\Action;
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

class PostNummersTable
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

                TextColumn::make('post_lan')
                    ->label('Post Län')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'whitespace-nowrap'])
                    ->placeholder('—'),

                TextColumn::make('total_count')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('count')
                    ->label('Count')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('Tele')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('house')
                    ->label('Hus')
                    ->numeric()
                    ->sortable()
                    ->grow(false)
                    ->extraAttributes(['class' => 'whitespace-nowrap text-right'])
                    ->placeholder('—'),

                TextColumn::make('computed_progress')
                    ->label('Done')
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
                    ->grow(false),

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

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
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
                Action::make('run')
                    ->label('Run')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Queue Ratsit/Hitta Scraper')
                    ->modalDescription(fn ($record) => "This will queue the post_ort_update.mjs script for post nummer: {$record->post_nummer}. The job will run in the background.")
                    ->modalSubmitActionLabel('Queue Job')
                    ->action(function ($record) {
                        // Dispatch the job to the queue
                        ProcessPostNummer::dispatch($record->post_nummer);

                        // Optimistically reflect in UI immediately
                        $record->update([
                            'status' => 'running',
                            'is_active' => true,
                        ]);

                        Notification::make()
                            ->title('Job Queued')
                            ->body("Update queued for post nummer: {$record->post_nummer}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'running' && $record->status !== 'complete' && $record->status !== 'empty'),

                Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Pause Processing')
                    ->modalDescription(fn ($record) => "This will stop processing post nummer: {$record->post_nummer}. Progress will be saved and can be resumed later.")
                    ->modalSubmitActionLabel('Pause Job')
                    ->action(function ($record) {
                        // Update status to pending (will be picked up for resume)
                        $record->update([
                            'status' => 'pending',
                            'is_active' => false,
                        ]);

                        Notification::make()
                            ->title('Job Paused')
                            ->body("Processing paused for post nummer: {$record->post_nummer}. Progress has been saved.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'running'),

                Action::make('update')
                    ->label('Update')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Re-run Complete Post Nummer')
                    ->modalDescription(fn ($record) => "This will re-run the scraper for post nummer: {$record->post_nummer}. The job will run in the background.")
                    ->modalSubmitActionLabel('Queue Job')
                    ->action(function ($record) {
                        // Reset progress for fresh run
                        $record->update([
                            'status' => 'running',
                            'is_active' => true,
                            'progress' => 0,
                            'count' => 0,
                            'phone' => 0,
                            'house' => 0,
                            'last_processed_page' => 0,
                            'processed_count' => 0,
                        ]);

                        // Dispatch the job to the queue
                        ProcessPostNummer::dispatch($record->post_nummer);

                        Notification::make()
                            ->title('Job Queued')
                            ->body("Update queued for post nummer: {$record->post_nummer}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'complete'),

                Action::make('checkTotals')
                    ->label('Check')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Check Hitta Totals')
                    ->modalDescription(fn ($record) => "This will run a light check to fetch only total results for post nummer: {$record->post_nummer} without processing all pages.")
                    ->modalSubmitActionLabel('Run Check')
                    ->action(function ($record) {
                        CheckHittaTotals::dispatch($record->post_nummer);
                        Notification::make()
                            ->title('Totals Check Queued')
                            ->body("Totals check queued for post nummer: {$record->post_nummer}")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkResetValues')
                        ->label('Bulk Reset Values')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Reset Selected Post Nummer Values')
                        ->modalDescription('This will reset status, is_active, progress, count, total_count, phone, house, is_pending, is_complete for all selected post nummers.')
                        ->action(function (Collection $records): void {
                            $reset = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => null,
                                    'is_active' => false,
                                    'progress' => 0,
                                    'count' => 0,
                                    'total_count' => 0,
                                    'phone' => 0,
                                    'house' => 0,
                                    'is_pending' => false,
                                    'is_complete' => false,
                                ]);
                                $reset++;
                            }
                            Notification::make()
                                ->title('Bulk Reset Complete')
                                ->body("Reset {$reset} post nummer row(s).")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),
                    BulkAction::make('queueRun')
                        ->label('Queue Run')
                        ->icon('heroicon-o-play')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Post Ort Run')
                        ->modalDescription('Queue post_ort_update.mjs scraper for all selected post nummers. Already running records will be skipped.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'running') {
                                    continue;
                                }

                                ProcessPostNummer::dispatch($record->post_nummer);
                                $queued++;

                                // Optimistic UI update so table reflects change immediately
                                $record->update([
                                    'status' => 'running',
                                    'is_active' => true,
                                ]);
                            }

                            Notification::make()
                                ->title('Jobs Queued')
                                ->body("Queued {$queued} job(s). They will process in the background.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),
                    DeleteBulkAction::make(),

                    BulkAction::make('bulkCheckTotals')
                        ->label('Check Totals')
                        ->icon('heroicon-o-magnifying-glass')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Check Hitta Totals')
                        ->modalDescription('Run a light totals check for selected rows that are not already empty (status and total_count both null). Empty rows will be skipped.')
                        ->action(function (Collection $records): void {
                            $queued = 0;
                            $skipped = 0;
                            foreach ($records as $record) {
                                // Skip rows already empty (status = 'empty')
                                if ($record->status === 'empty') {
                                    $skipped++;

                                    continue;
                                }
                                CheckHittaTotals::dispatch($record->post_nummer);
                                $queued++;
                            }
                            Notification::make()
                                ->title('Totals Check')
                                ->body("Queued {$queued} check(s). Skipped {$skipped} empty row(s).")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->closeModalByClickingAway(false),
                ]),
            ])
            ->defaultSort('post_nummer', 'asc')
            ->modifyQueryUsing(function ($query) {
                return $query->orderByRaw("CASE 
                    WHEN status = 'running' THEN 0 
                    WHEN status = 'pending' THEN 1 
                    ELSE 2 
                END")
                    ->orderBy('post_nummer', 'asc');
            });
    }
}
