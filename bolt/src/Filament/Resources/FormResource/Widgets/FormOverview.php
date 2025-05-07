<?php

namespace LaraExperts\Bolt\Filament\Resources\FormResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use LaraExperts\Bolt\Models\Form; // Use the correct class

class FormOverview extends BaseWidget
{
    public Form $record; // Update the type to LaraExperts\Bolt\Models\Form

    protected function getStats(): array
    {
        return [
            Stat::make('fields', $this->record->fields()->count())->label(__('Fields')),
            Stat::make('responses', $this->record->responses()->count())->label(__('Responses')),
            Stat::make('fields_responses', $this->record->fieldsResponses()->count())->label(__('Fields Responses')),
        ];
    }
}