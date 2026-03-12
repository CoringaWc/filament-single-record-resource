<?php

namespace Workbench\App\Filament\Resources\Wallets;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\Wallets\Pages\ListWallets;
use Workbench\App\Filament\Resources\Wallets\Pages\ViewWallet;
use Workbench\App\Filament\Resources\Wallets\RelationManagers\CompaniesRelationManager;
use Workbench\App\Filament\Resources\Wallets\Schemas\WalletForm;
use Workbench\App\Filament\Resources\Wallets\Schemas\WalletInfolist;
use Workbench\App\Filament\Resources\Wallets\Tables\WalletsTable;
use Workbench\App\Models\Wallet;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
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
            'index' => ListWallets::route('/'),
            'view' => ViewWallet::route('/{record}'),
        ];
    }
}
