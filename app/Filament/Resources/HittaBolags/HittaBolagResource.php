<?php

namespace App\Filament\Resources\HittaBolags;

use App\Filament\Resources\HittaBolags\Pages\CreateHittaBolag;
use App\Filament\Resources\HittaBolags\Pages\EditHittaBolag;
use App\Filament\Resources\HittaBolags\Pages\ListHittaBolags;
use App\Models\HittaBolag;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class HittaBolagResource extends Resource
{
    protected static ?string $model = HittaBolag::class;

    protected static ?string $navigationLabel = 'Hitta Bolag';

    protected static ?string $modelLabel = 'Hitta Bolag';

    protected static ?string $pluralModelLabel = 'Hitta Bolag';

    protected static UnitEnum | string | null $navigationGroup = 'Databases';

    protected static ?int $navigationSort = 2; // After Hitta.se

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\TextInput::make('juridiskt_namn')->label('Juridiskt namn')->columnSpan(6),
                Forms\Components\TextInput::make('org_nr')->label('Org.nr')->columnSpan(3),
                Forms\Components\DatePicker::make('registreringsdatum')->label('Registreringsdatum')->columnSpan(3),
                Forms\Components\TextInput::make('bolagsform')->label('Bolagsform')->columnSpan(4),
                Forms\Components\Repeater::make('sni_branch')
                    ->schema([
                        Forms\Components\TextInput::make('branch')->label('Branch'),
                    ])->collapsed()->columnSpan(8),
                Forms\Components\TextInput::make('gatuadress')->label('Adress')->columnSpan(6),
                Forms\Components\TextInput::make('postnummer')->columnSpan(3),
                Forms\Components\TextInput::make('postort')->columnSpan(3),
                Forms\Components\TextInput::make('telefon')->columnSpan(6),
                Forms\Components\TextInput::make('link')->columnSpan(6),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('juridiskt_namn')->label('Juridiskt namn')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('org_nr')->label('Org.nr')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('bolagsform')->label('Bolagsform')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('registreringsdatum')->label('Registreringsdatum')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('gatuadress')->label('Adress')->toggleable(),
                Tables\Columns\TextColumn::make('postnummer')->toggleable(),
                Tables\Columns\TextColumn::make('postort')->toggleable(),
                Tables\Columns\TextColumn::make('telefon')->label('Telefon')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('link')->label('Länk')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add filters later if needed
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHittaBolags::route('/'),
            'create' => CreateHittaBolag::route('/create'),
            'edit' => EditHittaBolag::route('/{record}/edit'),
        ];
    }
}
