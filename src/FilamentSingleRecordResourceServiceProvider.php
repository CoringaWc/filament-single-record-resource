<?php

namespace CoringaWc\FilamentSingleRecordResource;

use CoringaWc\FilamentSingleRecordResource\Testing\TestsFilamentSingleRecordResource;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentSingleRecordResourceServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-single-record-resource';

    public static string $viewNamespace = 'filament-single-record-resource';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('coringawc/filament-single-record-resource');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        $stubsPath = __DIR__ . '/../stubs/';

        if (app()->runningInConsole() && is_dir($stubsPath)) {
            foreach (app(Filesystem::class)->files($stubsPath) as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-single-record-resource/{$file->getFilename()}"),
                ], 'filament-single-record-resource-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentSingleRecordResource);
    }

    protected function getAssetPackageName(): string
    {
        return 'coringawc/filament-single-record-resource';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        $assets = [];

        $cssPath = __DIR__ . '/../resources/dist/filament-single-record-resource.css';

        if (file_exists($cssPath)) {
            $assets[] = Css::make('filament-single-record-resource-styles', $cssPath);
        }

        $jsPath = __DIR__ . '/../resources/dist/filament-single-record-resource.js';

        if (file_exists($jsPath)) {
            $assets[] = Js::make('filament-single-record-resource-scripts', $jsPath);
        }

        return $assets;
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-single-record-resource_table',
        ];
    }
}
