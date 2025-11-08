<?php

namespace App\Filament\Resources\PostNummers\Tables;

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
                    ->label('Post Nummer')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('post_ort')
                    ->label('Post Ort')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('post_lan')
                    ->label('Post Län')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('total_count')
                    ->label('Total Count')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'running' => 'warning',
                        'complete' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

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
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                Action::make('update')
                    ->label('Update')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Queue Ratsit/Hitta Scraper')
                    ->modalDescription(fn ($record) => "This will queue the ratsit_hitta.mjs script for post nummer: {$record->post_nummer}. The job will run in the background.")
                    ->modalSubmitActionLabel('Queue Job')
                    ->action(function ($record) {
                        // Check if already running
                        if ($record->status === 'running') {
                            Notification::make()
                                ->title('Already Running')
                                ->body("Post nummer {$record->post_nummer} is already being processed")
                                ->warning()
                                ->send();

                            return;
                        }

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
                    ->disabled(fn ($record) => $record->status === 'running'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('queueUpdate')
                        ->label('Queue Update')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Post Ort Update')
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
                ]),
            ])
            ->defaultSort('post_nummer', 'asc');
    }
}
