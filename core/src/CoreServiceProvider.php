<?php

namespace LaraExperts\Core;

use Composer\InstalledVersions;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CoreServiceProvider extends PackageServiceProvider
{
    public static string $name = 'form';

    public function packageBooted(): void
    {
        if (class_exists(AboutCommand::class) && class_exists(InstalledVersions::class)) {
            $packages = collect([
                'helen',
                'bolt-pro',
                'thunder',
                'hermes',
                'qr',
                'bolt',
                'sky',
                'wind',
                'dynamic-dashboard',
                'rhea',
                'artemis',
                'matrix-choice',
                'core',
                'popover',
                'accordion',
                'hera',
            ])
                ->filter(fn (string $package): bool => InstalledVersions::isInstalled("lara-experts/{$package}"))
                ->mapWithKeys(fn ($package) => [$package => InstalledVersions::getPrettyVersion("lara-experts/{$package}")])
                ->toArray();

            AboutCommand::add('Form ', $packages);
        }
        // let me have my fun 🤷🏽‍
        Blade::directive('form', function () {
            return '<span class="text-primary-700 group"><span class="font-semibold text-primary-600 group-hover:text-primary-500 transition ease-in-out duration-300">Lara&nbsp;<span class="line-through italic text-primary-500 group-hover:text-primary-600 transition ease-in-out duration-300">Z</span>eus</span></span>';
        });

        FilamentAsset::register([
            Css::make('filament-lara-experts', __DIR__ . '/../resources/dist/lara-experts.css'),
            Js::make('filament-lara-experts', __DIR__ . '/../resources/dist/plugin.js'),
        ], 'lara-experts');
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasAssets()
            ->hasConfigFile()
            ->hasViews('form');
    }

    public static function setThemePath(string $path): void
    {
        $viewPath = 'form::themes.' . config('form.theme') . '.' . $path;

        // check the app folder
        $folder = resource_path('views/vendor/form/themes/' . config('form.theme') . '/' . $path);

        if (! is_dir($folder)) {
            // check artemis folder
            $folder = base_path('vendor/lara-experts/artemis/resources/views/themes/' . config('form.theme') . '/' . $path);
            if (! is_dir($folder)) {
                $viewPath = 'form::themes.form.' . $path;
            }
        }

        View::share($path . 'Theme', $viewPath);
        App::singleton($path . 'Theme', function () use ($viewPath) {
            return $viewPath;
        });
    }
}
