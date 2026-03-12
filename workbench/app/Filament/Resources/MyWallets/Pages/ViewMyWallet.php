<?php

namespace Workbench\App\Filament\Resources\MyWallets\Pages;

use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecord;
use Filament\Resources\Pages\ViewRecord;
use Workbench\App\Filament\Resources\MyWallets\MyWalletResource;

class ViewMyWallet extends ViewRecord
{
    use HasSingleRecord;

    protected static string $resource = MyWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
