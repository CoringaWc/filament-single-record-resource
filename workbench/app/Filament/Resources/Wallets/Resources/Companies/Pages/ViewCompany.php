<?php

namespace Workbench\App\Filament\Resources\Wallets\Resources\Companies\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\CompanyResource;

class ViewCompany extends ViewRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
