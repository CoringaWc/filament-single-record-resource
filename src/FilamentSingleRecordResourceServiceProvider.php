<?php

namespace CoringaWc\FilamentSingleRecordResource;

use CoringaWc\FilamentSingleRecordResource\Testing\TestsFilamentSingleRecordResource;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentSingleRecordResourceServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-single-record-resource';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->askToStarRepoOnGitHub('coringawc/filament-single-record-resource');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        Testable::mixin(new TestsFilamentSingleRecordResource);
    }
}
