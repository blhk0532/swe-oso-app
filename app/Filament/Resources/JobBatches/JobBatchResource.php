<?php

namespace App\Filament\Resources\JobBatches;

use App\Filament\Resources\JobBatches\Pages\CreateJobBatch;
use App\Filament\Resources\JobBatches\Pages\EditJobBatch;
use App\Filament\Resources\JobBatches\Pages\ListJobBatches;
use App\Filament\Resources\JobBatches\Pages\ViewJobBatch;
use App\Filament\Resources\JobBatches\Schemas\JobBatchForm;
use App\Filament\Resources\JobBatches\Schemas\JobBatchInfolist;
use App\Filament\Resources\JobBatches\Tables\JobBatchesTable;
use App\Models\JobBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class JobBatchResource extends Resource
{
    protected static ?string $model = JobBatch::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Job Batches';

    protected static ?string $modelLabel = 'Job Batch';

    protected static ?string $pluralModelLabel = 'Job Batches';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return JobBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JobBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobBatchesTable::configure($table);
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
            'index' => ListJobBatches::route('/'),
            'create' => CreateJobBatch::route('/create'),
            'view' => ViewJobBatch::route('/{record}'),
            'edit' => EditJobBatch::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
