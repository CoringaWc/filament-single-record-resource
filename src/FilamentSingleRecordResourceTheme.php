<?php

namespace CoringaWc\FilamentSingleRecordResource;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;

class FilamentSingleRecordResourceTheme implements Plugin
{
    public function getId(): string
    {
        return 'filament-single-record-resource';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('filament-single-record-resource', __DIR__ . '/../resources/dist/filament-single-record-resource.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Gray,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'success' => Color::Green,
            ])
            ->theme('filament-single-record-resource');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
