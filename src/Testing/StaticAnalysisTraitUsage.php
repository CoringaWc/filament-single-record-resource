<?php

namespace CoringaWc\FilamentSingleRecordResource\Testing;

use CoringaWc\FilamentSingleRecordResource\Contracts\SingleRecordResolvableResource;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecordResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class StaticAnalysisTraitUsage
{
    public static function ensureTraitsAreUsed(): void {}
}

class StaticAnalysisSingleRecordResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    protected static ?string $model = Model::class;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }
}

class StaticAnalysisViewRecordPage extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = StaticAnalysisSingleRecordResource::class;
}

class StaticAnalysisEditRecordPage extends EditRecord
{
    use HasSingleRecord;

    protected static string $resource = StaticAnalysisSingleRecordResource::class;
}
