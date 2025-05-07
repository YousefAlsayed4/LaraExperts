<?php

namespace LaraExperts\Bolt\Fields\Classes;

use LaraExperts\Accordion\Forms\Accordion;
use LaraExperts\Accordion\Forms\Accordions;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Fields\FieldsContract;

class DatePicker extends FieldsContract
{
    public string $renderClass = \Filament\Forms\Components\DatePicker::class;

    public int $sort = 6;

    public function title(): string
    {
        return __('Date Picker');
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function description(): string
    {
        return __('full date picker');
    }

    public static function getOptions(?array $sections = null, ?array $field = null): array
    {
        return [
            Accordions::make('check-list-options')
                ->accordions([
                    Accordion::make('general-options')
                        ->label(__('General Options'))
                        ->icon('iconpark-checklist-o')
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
            self::hiddenHtmlID(),
            self::hiddenHintOptions(),
            self::hiddenRequired(),
            self::hiddenColumnSpanFull(),
            self::hiddenHiddenLabel(),
            self::hiddenVisibility(),
        ];
    }
}
