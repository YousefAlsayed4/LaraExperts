<?php

namespace LaraExperts\Bolt;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use LaraExperts\Bolt\Filament\Resources\CollectionResource;
use LaraExperts\Bolt\Filament\Resources\FormResource;
use LaraExperts\Core\Concerns\CanGloballySearch;
use LaraExperts\Core\Concerns\HasRouteNamePrefix;
use LaraExperts\FilamentPluginTools\Concerns\CanDisableBadges;

final class BoltPlugin implements Plugin
{
    use CanDisableBadges;
    use CanGloballySearch;
    use Configuration;
    use EvaluatesClosures;
    use HasRouteNamePrefix;


    public array $defaultGloballySearchableAttributes = [
        CollectionResource::class => ['name', 'values'],
        FormResource::class => ['name', 'slug'],
    ];

    public function getId(): string
    {
        return 'form-bolt';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                CollectionResource::class,
                FormResource::class,
            ]);
    }

    public static function make(): static
    {
        return new self;
    }

    public static function get(): static
    {
        // @phpstan-ignore-next-line
        return filament('form-bolt');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
