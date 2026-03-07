<?php

namespace Workbench\App\Filament\Resources\MyWallets\Resources\Companies\Resources\Products\Pages;

use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\Resources\Products\ProductResource;

class ViewProduct extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
