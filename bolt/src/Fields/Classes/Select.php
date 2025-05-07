<?php

namespace LaraExperts\Bolt\Fields\Classes;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use LaraExperts\Accordion\Forms\Accordion;
use LaraExperts\Accordion\Forms\Accordions;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Fields\FieldsContract;
use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\FieldResponse;

class Select extends FieldsContract
{
    public string $renderClass = \Filament\Forms\Components\Select::class;

    public int $sort = 2;

    public function title(): string
    {
        return __('Select Menu');
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function description(): string
    {
        return __('select single or multiple items from a dropdown list');
    }

    public static function getOptions(?array $sections = null, ?array $field = null): array
    {
        return [
            self::dataSource(),
            Toggle::make('options.allow_multiple')->label(__('Allow Multiple')),
            Accordions::make('options')
                ->activeAccordion(1)
                ->accordions([
                    Accordion::make('general-options')
                        ->label(__('General Options'))
                        ->icon('iconpark-checklist-o')
                        ->columns()
                        ->schema([
                            self::required(),
                            self::columnSpanFull(),
                            self::hiddenLabel(),
                            self::htmlID(),
                        ]),
                    self::hintOptions(),
                    self::visibility($sections),
                    // @phpstan-ignore-next-line
                    // ...Bolt::hasPro() ? \LaraZeus\BoltPro\Facades\GradeOptions::schema($field) : [],
                    Bolt::getCustomSchema('field', resolve(static::class)) ?? [],
                ]),
        ];
    }

    public static function getOptionsHidden(): array
    {
        return [
            // @phpstan-ignore-next-line
            // Bolt::hasPro() ? \LaraZeus\BoltPro\Facades\GradeOptions::hidden() : [],
            ...Bolt::getHiddenCustomSchema('field', resolve(static::class)) ?? [],
            self::hiddenVisibility(),
            self::hiddenHtmlID(),
            self::hiddenHintOptions(),
            self::hiddenRequired(),
            self::hiddenColumnSpanFull(),
            self::hiddenHiddenLabel(),
            Hidden::make('options.dataSource'),
            Hidden::make('options.allow_multiple')->default(false),
        ];
    }

    public function getResponse(Field $field, FieldResponse $resp): string
    {
        return $this->getCollectionsValuesForResponse($field, $resp);
    }

    // @phpstan-ignore-next-line
    public function appendFilamentComponentsOptions($component, $formField, bool $hasVisibility = false)
{
    parent::appendFilamentComponentsOptions($component, $formField, $hasVisibility);

    // Get the options for the component
    $options = FieldsContract::getFieldCollectionItemsList($formField);

    $component = $component
        ->searchable()
        ->preload()
        ->options($options);

    // Ensure $formField->options exists and is an array
    $formOptions = $formField->options ?? [];

    if (isset($formOptions['allow_multiple']) && $formOptions['allow_multiple']) {
        $component = $component->multiple();
    }

    if (isset($formOptions['htmlId']) && request()->filled($formOptions['htmlId'])) {
        $component = $component->default(request($formOptions['htmlId']));
        // todo set default items for datasources
    } elseif ($selected = collect($options)->where('itemIsDefault', true)->pluck('itemKey')->isNotEmpty()) {
        $component = $component->default($selected);
    }

    return $component->live();
}
}
