<?php

namespace LaraExperts\Bolt\Filament\Resources\CollectionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use LaraExperts\Bolt\Filament\Resources\CollectionResource;

class ListCollections extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
