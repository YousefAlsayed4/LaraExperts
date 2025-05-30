<?php

namespace LaraExperts\Bolt\Filament\Resources\FormResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use LaraExperts\Bolt\BoltPlugin;
use LaraExperts\Bolt\Filament\Resources\FormResource;
use LaraExperts\Bolt\Models\Form;

/**
 * @property Form $record.
 */
class EditForm extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = FormResource::class;

    public function areFormActionsSticky(): bool
    {
        return BoltPlugin::get()->isFormActionsAreSticky();
    }

    public function getTitle(): string | Htmlable
    {
        return __('Edit Form');
    }

    public static function getNavigationLabel(): string
    {
        return __('Edit Form');
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Action::make('open')
                ->label(__('Open'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->tooltip(__('open form'))
                ->color('warning')
                ->url(fn () => route(BoltPlugin::get()->getRouteNamePrefix() . 'bolt.form.show', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
