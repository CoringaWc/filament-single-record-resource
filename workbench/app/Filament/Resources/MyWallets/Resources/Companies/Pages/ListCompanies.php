<?php

namespace Workbench\App\Filament\Resources\MyWallets\Resources\Companies\Pages;

use Filament\Resources\Pages\ListRecords;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\CompanyResource;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;
}
