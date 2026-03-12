<?php

namespace Workbench\App\Filament\Resources\Wallets\Resources\Companies;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\Companies\Schemas\CompanyForm;
use Workbench\App\Filament\Resources\Companies\Schemas\CompanyInfolist;
use Workbench\App\Filament\Resources\Companies\Tables\CompaniesTable;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\Pages\ListCompanies;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\Pages\ViewCompany;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\RelationManagers\ProductsRelationManager;
use Workbench\App\Filament\Resources\Wallets\WalletResource;
use Workbench\App\Models\Company;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $parentResource = WalletResource::class;

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CompanyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'products' => ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'view' => ViewCompany::route('/{record}'),
        ];
    }
}
