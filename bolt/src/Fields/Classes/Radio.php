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

class Radio extends FieldsContract
{
    public string $renderClass = \Filament\Forms\Components\Radio::class;

    public int $sort = 4;

    public function title(): string
    {
        return __('Radio');
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function description(): string
    {
        return __('single choice from a datasource');
    }

    public static function getOptions(?array $sections = null, ?array $field = null): array
    {
        return [
            self::dataSource(),
            Accordions::make('check-list-options')
                ->accordions([
                    Accordion::make('general-options')
                        ->label(__('General Options'))
                        // ->icon('iconpark-checklist-o')
                        ->schema([
                            self::required(),
                            Toggle::make('options.is_inline')->label(__('Is inline')),
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
            Hidden::make('options.is_inline')->default(false),
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
    
        $options = FieldsContract::getFieldCollectionItemsList($formField);
    
        $component = $component->options($options);
    
        // Ensure $formField->options exists and is an array
        $formOptions = $formField->options ?? [];
    
        if (isset($formOptions['is_inline']) && $formOptions['is_inline']) {
            $component->inline();
        }
    
        if (isset($formOptions['htmlId']) && request()->filled($formOptions['htmlId'])) {
            $component = $component->default(request($formOptions['htmlId']));
            // todo set default items for datasources
        } elseif ($selected = $options->where('itemIsDefault', true)->pluck('itemKey')->isNotEmpty()) {
            $component = $component->default($selected);
        }
    
        return $component->live();
    }
}
