<?php

namespace App\Filament\Resources\HittaDatas;

use App\Filament\Resources\HittaDatas\Pages\CreateHittaData;
use App\Filament\Resources\HittaDatas\Pages\EditHittaData;
use App\Filament\Resources\HittaDatas\Pages\ListHittaDatas;
use App\Models\HittaData;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class HittaDataResource extends Resource
{
    protected static ?string $model = HittaData::class;

    protected static ?string $navigationLabel = 'Hitta Data';

    protected static ?string $modelLabel = 'Hitta Data';

    protected static ?string $pluralModelLabel = 'Hitta Data';

    protected static UnitEnum | string | null $navigationGroup = 'Databases';

    protected static ?int $navigationSort = 3; // After Hitta Bolag

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\TextInput::make('personnamn')->label('Personnamn')->columnSpan(6),
                Forms\Components\TextInput::make('alder')->label('Ålder')->columnSpan(3),
                Forms\Components\TextInput::make('kon')->label('Kön')->columnSpan(3),
                Forms\Components\TextInput::make('gatuadress')->label('Adress')->columnSpan(6),
                Forms\Components\TextInput::make('postnummer')->columnSpan(3),
                Forms\Components\TextInput::make('postort')->columnSpan(3),
                Forms\Components\TextInput::make('telefon')->label('Telefon')->columnSpan(6),
                Forms\Components\TextInput::make('karta')->label('Karta')->columnSpan(6),
                Forms\Components\TextInput::make('link')->label('Länk')->columnSpan(6),
                Forms\Components\TextInput::make('bostadstyp')->label('Bostadstyp')->columnSpan(6),
                Forms\Components\TextInput::make('bostadspris')->label('Bostadspris')->columnSpan(6),
                Forms\Components\Toggle::make('is_active')->label('Aktiv')->columnSpan(4),
                Forms\Components\Toggle::make('is_telefon')->label('Har telefon')->columnSpan(4),
                Forms\Components\Toggle::make('is_ratsit')->label('Har Ratsit')->columnSpan(4),
            ]),
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
            'index' => ListHittaDatas::route('/'),
            'create' => CreateHittaData::route('/create'),
            'edit' => EditHittaData::route('/{record}/edit'),
        ];
    }
}
