<?php

namespace LaraExperts\Bolt\Fields\Classes;

use Filament\Forms\Components\ColorPicker as ColorPickerAlias;
use Filament\Forms\Components\Hidden;
use LaraExperts\Accordion\Forms\Accordion;
use LaraExperts\Accordion\Forms\Accordions;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Fields\FieldsContract;

class ColorPicker extends FieldsContract
{
    public string $renderClass = ColorPickerAlias::class;

    public int $sort = 9;

    public function title(): string
    {
        return __('Color Picker');
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function description(): string
    {
        return __('pick a color with rgb, rgba or hsl');
    }

    public static function getOptions(?array $sections = null): array
    {
        return [
            Accordions::make('check-list-options')
                ->accordions([
                    Accordion::make('general-options')
                        ->label(__('General Options'))
                        ->icon('iconpark-checklist-o')
                        ->schema([
                            \Filament\Forms\Components\Select::make('options.colorType')
                                ->label(__('Color Type'))
                                ->options([
                                    'hsl' => 'hsl',
                                    'rgb' => 'rgb',
                                    'rgba' => 'rgba',
                                ]),
                            self::required(),
                            self::columnSpanFull(),
                            self::hiddenLabel(),
                            self::htmlID(),
                        ]),
                    self::hintOptions(),
                    self::visibility($sections),
                    Bolt::getCustomSchema('field', resolve(static::class)) ?? [],
                ]),
        ];
    }

    public static function getOptionsHidden(): array
    {
        return [
            ...Bolt::getHiddenCustomSchema('field', resolve(static::class)) ?? [],
            Hidden::make('options.colorType'),
            self::hiddenHtmlID(),
            self::hiddenHintOptions(),
            self::hiddenRequired(),
            self::hiddenColumnSpanFull(),
            self::hiddenHiddenLabel(),
            self::hiddenVisibility(),
        ];
    }

    // @phpstan-ignore-next-line
    public function appendFilamentComponentsOptions($component, $formField, bool $hasVisibility = false)
    {
        parent::appendFilamentComponentsOptions($component, $formField, $hasVisibility);

        if (! empty($formField['options']['colorType'])) {
            call_user_func([$component, $formField['options']['colorType']]);
        }

        return $component;
    }
}
