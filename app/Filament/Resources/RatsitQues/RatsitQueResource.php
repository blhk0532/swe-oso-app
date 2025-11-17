<?php

namespace App\Filament\Resources\RatsitQues;

use App\Filament\Resources\RatsitQues\Pages\CreateRatsitQue;
use App\Filament\Resources\RatsitQues\Pages\EditRatsitQue;
use App\Filament\Resources\RatsitQues\Pages\ListRatsitQues;
use App\Filament\Resources\RatsitQues\Schemas\RatsitQueForm;
use App\Filament\Resources\RatsitQues\Tables\RatsitQuesTable;
use App\Models\RatsitQue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RatsitQueResource extends Resource
{
    protected static ?string $model = RatsitQue::class;

    protected static ?string $recordTitleAttribute = 'personnamn';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Ratsit Queue';

    protected static string | UnitEnum | null $navigationGroup = 'Databases';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return RatsitQueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatsitQuesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatsitQues::route('/'),
            'create' => CreateRatsitQue::route('/create'),
            'edit' => EditRatsitQue::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->personnamn ?? 'Unnamed';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['personnamn', 'personnummer', 'gatuadress', 'postnummer', 'postort'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->gatuadress) {
            $details['Address'] = $record->gatuadress;
        }

        if ($record->postnummer || $record->postort) {
            $details['Location'] = trim("{$record->postnummer} {$record->postort}");
        }

        if ($record->personnummer) {
            $details['Personal Number'] = $record->personnummer;
        }

        return $details;
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Hide Blog > Posts from sidebar navigation.
        return false;
    }
}
