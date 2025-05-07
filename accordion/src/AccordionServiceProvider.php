<?php

namespace LaraExperts\Accordion;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AccordionServiceProvider extends PackageServiceProvider
{
    public static string $name = 'form-accordion';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }
}
