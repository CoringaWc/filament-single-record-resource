<?php

namespace Workbench\App\Providers\Filament;

use CoringaWc\FilamentSingleRecordResource\FilamentSingleRecordResourcePlugin;
use Filament\Forms\Components\DateTimePicker;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        Schema::configureUsing(function (Schema $schema): void {
            $schema
                ->defaultDateDisplayFormat('d/m/Y')
                ->defaultDateTimeDisplayFormat('d/m/Y H:i:s')
                ->defaultTimeDisplayFormat('H:i:s');
        });

        Table::configureUsing(function (Table $table): void {
            $table
                ->defaultDateDisplayFormat('d/m/Y')
                ->defaultDateTimeDisplayFormat('d/m/Y H:i:s')
                ->defaultTimeDisplayFormat('H:i:s');
        });

        DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
            $dateTimePicker
                ->defaultDateDisplayFormat('d/m/Y')
                ->defaultDateTimeDisplayFormat('d/m/Y H:i:s')
                ->defaultTimeDisplayFormat('H:i:s');
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: __DIR__ . '/../../Filament/Resources', for: 'Workbench\\App\\Filament\\Resources')
            ->discoverPages(in: __DIR__ . '/../../Filament/Pages', for: 'Workbench\\App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: __DIR__ . '/../../Filament/Widgets', for: 'Workbench\\App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(FilamentSingleRecordResourcePlugin::make());
    }
}
