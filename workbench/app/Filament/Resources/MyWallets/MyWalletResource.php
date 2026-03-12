<?php

namespace Workbench\App\Filament\Resources\MyWallets;

use CoringaWc\FilamentSingleRecordResource\Traits\HasSingleRecordResource;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\MyWallets\Pages\ViewMyWallet;
use Workbench\App\Filament\Resources\MyWallets\RelationManagers\CompaniesRelationManager;
use Workbench\App\Filament\Resources\Wallets\Schemas\WalletForm;
use Workbench\App\Filament\Resources\Wallets\Schemas\WalletInfolist;
use Workbench\App\Filament\Resources\Wallets\Tables\WalletsTable;
use Workbench\App\Models\Wallet;

class MyWalletResource extends Resource
{
    use HasSingleRecordResource;

    protected static ?string $model = Wallet::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $slug = 'my-wallets';

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function getModelLabel(): string
    {
        return 'My Wallet';
    }

    public static function getPluralModelLabel(): string
    {
        return 'My Wallets';
    }

    public static function form(Schema $schema): Schema
    {
        return WalletForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WalletInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WalletsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CompaniesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewMyWallet::route('/'),
        ];
    }
}
