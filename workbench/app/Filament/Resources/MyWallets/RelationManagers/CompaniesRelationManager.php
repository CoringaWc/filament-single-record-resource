<?php

namespace Workbench\App\Filament\Resources\MyWallets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\CompanyResource;

class CompaniesRelationManager extends RelationManager
{
    protected static string $relationship = 'companies';

    protected static ?string $relatedResource = CompanyResource::class;

    public function isReadOnly(): bool
    {
        return false;
    }
}
