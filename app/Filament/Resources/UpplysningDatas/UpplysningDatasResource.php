<?php

namespace App\Filament\Resources\UpplysningDatas;

use App\Filament\Resources\UpplysningDatas\Pages\CreateUpplysningData;
use App\Filament\Resources\UpplysningDatas\Pages\EditUpplysningData;
use App\Filament\Resources\UpplysningDatas\Pages\ListUpplysningDatas;
use App\Models\UpplysningData;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
 
class UpplysningDatasResource extends Resource
{
    protected static ?string $model = UpplysningData::class;

    protected static ?string $navigationLabel = 'Upplysning';

    protected static ?string $modelLabel = 'Upplysning';

    protected static ?string $pluralModelLabel = 'Upplysning';

    protected static UnitEnum | string | null $navigationGroup = 'PERSON DATABASER';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Personal Information')
                ->schema([
                    Forms\Components\TextInput::make('personnamn')->label('Personnamn'),
                    Forms\Components\TextInput::make('alder')->label('Ålder'),
                    Forms\Components\TextInput::make('kon')->label('Kön'),
                ])
                ->columns(3),

            Section::make('Address Information')
                ->schema([
                    Forms\Components\TextInput::make('gatuadress')->label('Adress'),
                    Forms\Components\TextInput::make('postnummer')->label('Postnummer'),
                    Forms\Components\TextInput::make('postort')->label('Postort'),
                ])
                ->columns(3),

            Section::make('Contact Information')
                ->schema([
                    Forms\Components\TextInput::make('telefon')->label('Telefon'),
                    Forms\Components\TextInput::make('karta')->label('Karta'),
                    Forms\Components\TextInput::make('link')->label('Länk'),
                ])
                ->columns(1),

            Section::make('Property Information')
                ->schema([
                    Forms\Components\TextInput::make('bostadstyp')->label('Bostadstyp'),
                    Forms\Components\TextInput::make('bostadspris')->label('Bostadspris'),
                ])
                ->columns(2),

            Section::make('Status')
                ->schema([
                    Forms\Components\Toggle::make('is_active')->label('Aktiv')->default(true),
                    Forms\Components\Toggle::make('is_telefon')->label('Har telefon')->default(false),
                    Forms\Components\Toggle::make('is_ratsit')->label('Har Ratsit')->default(false),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('personnamn')->label('Personnamn')->searchable(),
                Tables\Columns\TextColumn::make('alder')->label('Ålder'),
                Tables\Columns\TextColumn::make('kon')->label('Kön'),
                Tables\Columns\TextColumn::make('gatuadress')->label('Adress')->searchable(),
                Tables\Columns\TextColumn::make('postnummer')->label('Postnummer'),
                Tables\Columns\TextColumn::make('postort')->label('Postort'),
                Tables\Columns\TextColumn::make('telefon')->label('Telefon'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktiv')->boolean(),
                Tables\Columns\IconColumn::make('is_telefon')->label('Telefon')->boolean(),
                Tables\Columns\IconColumn::make('is_ratsit')->label('Ratsit')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Skapad')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktiv'),
                Tables\Filters\TernaryFilter::make('is_telefon')->label('Har telefon'),
                Tables\Filters\TernaryFilter::make('is_ratsit')->label('Har Ratsit'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUpplysningDatas::route('/'),
            'create' => CreateUpplysningData::route('/create'),
            'edit' => EditUpplysningData::route('/{record}/edit'),
        ];
    }
}
