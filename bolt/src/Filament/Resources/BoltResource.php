<?php

namespace LaraExperts\Bolt\Filament\Resources;

use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use LaraExperts\Bolt\BoltPlugin;

class BoltResource extends Resource
{
    use Translatable;

    public static function canViewAny(): bool
    {
        return ! in_array(static::class, BoltPlugin::get()->getHiddenResources())
            && parent::canViewAny();
    }

    public static function getNavigationGroup(): ?string
    {
        return BoltPlugin::get()->getNavigationGroupLabel();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return BoltPlugin::get()->getGlobalAttributes(static::class);
    }
}
