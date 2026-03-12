<?php

namespace Workbench\App\Filament\Resources\Wallets\Resources\Companies\Resources\Products;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\Products\RelationManagers\NotesRelationManager;
use Workbench\App\Filament\Resources\Products\Schemas\ProductForm;
use Workbench\App\Filament\Resources\Products\Schemas\ProductInfolist;
use Workbench\App\Filament\Resources\Products\Tables\ProductsTable;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\CompanyResource;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\Resources\Products\Pages\ListProducts;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\Resources\Products\Pages\ViewProduct;
use Workbench\App\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $parentResource = CompanyResource::class;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'view' => ViewProduct::route('/{record}'),
        ];
    }
}
