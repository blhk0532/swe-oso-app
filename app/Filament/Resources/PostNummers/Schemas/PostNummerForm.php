<?php

namespace App\Filament\Resources\PostNummers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostNummerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Post Nummer Information')
                    ->schema([
                        TextInput::make('post_nummer')
                            ->label('Post Nummer')
                            ->required()
                            ->maxLength(6)
                            ->unique(ignoreRecord: true),

                        TextInput::make('post_ort')
                            ->label('Post Ort')
                            ->maxLength(255),

                        TextInput::make('post_lan')
                            ->label('Post Län')
                            ->maxLength(255),

                        TextInput::make('total_count')
                            ->label('Total Count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('count')
                            ->label('Count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('house')
                            ->label('House')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('progress')
                            ->label('Progress')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('last_processed_page')
                            ->label('Last Processed Page')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('processed_count')
                            ->label('Processed Count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'running' => 'Running',
                                'complete' => 'Complete',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_pending')
                            ->label('Is Pending')
                            ->default(true),

                        Toggle::make('is_complete')
                            ->label('Is Complete')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(false),
                    ])
                    ->columns(3),
            ]);
    }
}
