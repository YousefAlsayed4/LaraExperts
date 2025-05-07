<?php

namespace LaraExperts\Bolt\Filament\Resources\CollectionResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class EditCollectionWarning extends Widget
{
    protected int | string | array $columnSpan = 'full';

    public ?Model $record = null;

    protected static string $view = 'form::filament.resources.form-resource.widgets.edit-collection-warning';
}
