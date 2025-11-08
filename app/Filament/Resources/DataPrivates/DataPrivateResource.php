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

    //    protected static ?string $recordTitleAttribute = 'ps_personnamn';

    //    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUser;

    //    protected static ?string $navigationLabel = 'Private Data';

    //    protected static string | UnitEnum | null $navigationGroup = 'Data Management';

    //    protected static ?int $navigationSort = 1;

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
            'ps_personnamn',
            'ps_personnummer',
            'ps_fornamn',
            'ps_efternamn',
            'bo_postnummer',
            'bo_postort',
            'bo_kommun',
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
            'Personnummer' => $record->ps_personnummer,
            'Address' => $record->bo_gatuadress,
            'Postort' => $record->bo_postort,
        ];
    }
}
