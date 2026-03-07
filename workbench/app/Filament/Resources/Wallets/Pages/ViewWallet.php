<?php

namespace Workbench\App\Filament\Resources\Wallets\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Workbench\App\Filament\Resources\Wallets\WalletResource;

class ViewWallet extends ViewRecord
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
