<?php

namespace Workbench\App\Filament\Resources\Wallets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('user.name')->label('User'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
