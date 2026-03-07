<?php

namespace Workbench\App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Workbench\App\Filament\Resources\MyWallets\RelationManagers\CompaniesRelationManager as MyWalletCompaniesRelationManager;
use Workbench\App\Filament\Resources\Wallets\RelationManagers\CompaniesRelationManager as WalletCompaniesRelationManager;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('wallet_id')
                    ->label('Wallet')
                    ->relationship('wallet', 'id')
                    ->searchable()
                    ->preload()
                    ->hiddenOn([
                        WalletCompaniesRelationManager::class,
                        MyWalletCompaniesRelationManager::class,
                    ])
                    ->required(),
            ]);
    }
}
