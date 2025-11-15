<?php

namespace App\Filament\Resources\DataPrivates;

use App\Filament\Resources\DataPrivates\Pages\CreateDataPrivate;
use App\Filament\Resources\DataPrivates\Pages\EditDataPrivate;
use App\Filament\Resources\DataPrivates\Pages\ListDataPrivates;
use App\Filament\Resources\DataPrivates\Schemas\DataPrivateForm;
use App\Filament\Resources\DataPrivates\Tables\DataPrivatesTable;
use App\Models\DataPrivate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DataPrivateResource extends Resource
{
    protected static ?string $model = DataPrivate::class;

    protected static ?string $recordTitleAttribute = 'personnamn';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Personer Databas';

    protected static string | UnitEnum | null $navigationGroup = 'ADMINISTRATION';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return DataPrivateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataPrivatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataPrivates::route('/'),
            'create' => CreateDataPrivate::route('/create'),
            'edit' => EditDataPrivate::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'personnamn',
            'personnummer',
            'fornamn',
            'efternamn',
            'postnummer',
            'postort',
            'kommun',
        ];
    }

    /** @return Builder<DataPrivate> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var DataPrivate $record */
        return [
            'Personnummer' => $record->personnummer,
            'Address' => $record->gatuadress,
            'Postort' => $record->postort,
        ];
    }
}
