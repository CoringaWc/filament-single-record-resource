<?php

namespace Workbench\App\Filament\Resources\Wallets\Resources\Companies\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Workbench\App\Filament\Resources\Wallets\Resources\Companies\Resources\Products\ProductResource;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $relatedResource = ProductResource::class;

    public function isReadOnly(): bool
    {
        return false;
    }
}
