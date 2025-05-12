<?php

namespace LaraExperts\Bolt\Fields\Classes;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea as TextareaAlias;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use LaraExperts\Accordion\Forms\Accordion;
use LaraExperts\Accordion\Forms\Accordions;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Fields\FieldsContract;
use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\FieldResponse;
use LaraExperts\Bolt\Models\Response;

class Textarea extends FieldsContract
{
    public string $renderClass = TextareaAlias::class;

    public int $sort = 8;

    public function title(): string
    {
        return __('Textarea');
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function description(): string
    {
        return __('multi line textarea');
    }

    public static function getOptions(?array $sections = null, ?array $field = null): array
    {
        return [
            Accordions::make('check-list-options')
                ->columns()
                ->accordions([
                    Accordion::make('general-options')
                        ->label(__('General Options'))
                        // ->icon('iconpark-checklist-o')
                        ->schema([
                            TextInput::make('options.rows')
                                ->label(__('rows')),

                            TextInput::make('options.cols')
                                ->label(__('cols')),

                            TextInput::make('options.minLength')
                                ->label(__('min length')),

                            TextInput::make('options.maxLength')
                                ->label(__('max length')),

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
            Hidden::make('options.rows'),
            Hidden::make('options.cols'),
            Hidden::make('options.minLength'),
            Hidden::make('options.maxLength'),
        ];
    }

    // @phpstan-ignore-next-line
    public function appendFilamentComponentsOptions($component, $formField, bool $hasVisibility = false)
{
    parent::appendFilamentComponentsOptions($component, $formField, $hasVisibility);

    // Ensure $formField['options'] exists and is an array
    $options = $formField['options'] ?? [];

    if (isset($options['maxLength']) && filled($options['maxLength'])) {
        $component->maxLength($options['maxLength']);
    }
    if (isset($options['rows']) && filled($options['rows'])) {
        $component->rows($options['rows']);
    }
    if (isset($options['cols']) && filled($options['cols'])) {
        $component->cols($options['cols']);
    }

    return $component;
}

    public function getResponse(Field $field, FieldResponse $resp): string
    {
        return nl2br(strip_tags($resp->response));
    }

    public function TableColumn(Field $field): ?Column
    {
        return TextColumn::make('formData.' . $field->id)
            ->sortable(false)
            ->label($field->name)
            ->searchable(query: function (Builder $query, string $search): Builder {
                return $query
                    ->whereHas('fieldsResponses', function ($query) use ($search) {
                        $query->where('response', 'like', '%' . $search . '%');
                    });
            })
            ->getStateUsing(fn (Response $record) => $this->getFieldResponseValue($record, $field))
            ->html()
            ->limit(50)
            ->toggleable();
    }
}
