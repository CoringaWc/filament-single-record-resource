<?php

namespace Workbench\App\Filament\Resources\MyWallets\Resources\Companies\Pages;

use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use Filament\Resources\Pages\EditRecord;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\CompanyResource;

class EditCompany extends EditRecord
{
    use HasSingleRecord;

    protected static string $resource = CompanyResource::class;
}
