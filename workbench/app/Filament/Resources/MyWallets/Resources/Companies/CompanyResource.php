<?php

namespace Workbench\App\Filament\Resources\MyWallets\Resources\Companies;

use CoringaWc\FilamentSingleRecordResource\Contracts\SingleRecordResolvableResource;
use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecordResource;
use Filament\Resources\ParentResourceRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\Companies\Schemas\CompanyForm;
use Workbench\App\Filament\Resources\Companies\Schemas\CompanyInfolist;
use Workbench\App\Filament\Resources\Companies\Tables\CompaniesTable;
use Workbench\App\Filament\Resources\MyWallets\MyWalletResource;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\Pages\EditCompany;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\Pages\ListCompanies;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\Pages\ViewCompany;
use Workbench\App\Filament\Resources\MyWallets\Resources\Companies\RelationManagers\ProductsRelationManager;
use Workbench\App\Models\Company;

class CompanyResource extends Resource implements SingleRecordResolvableResource
{
    use HasSingleRecordResource;

    protected static ?string $model = Company::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $parentResource = MyWalletResource::class;

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return null;
    }

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
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
