<?php

namespace Workbench\App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Workbench\App\Filament\Resources\Companies\RelationManagers\ProductsRelationManager as CompanyProductsRelationManager;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\RelationManagers\ProductsRelationManager as MyWalletCompanyProductsRelationManager;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\RelationManagers\ProductsRelationManager as WalletCompanyProductsRelationManager;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->hiddenOn([
                        CompanyProductsRelationManager::class,
                        WalletCompanyProductsRelationManager::class,
                        MyWalletCompanyProductsRelationManager::class,
                    ])
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
