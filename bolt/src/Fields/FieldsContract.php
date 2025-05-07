<?php

namespace LaraExperts\Bolt\Fields;

use LaraExperts\Bolt\Concerns\CustomHasOptions;
use Filament\Actions\Exports\ExportColumn;
use Filament\Forms\Get;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use LaraExperts\Bolt\BoltPlugin;
use LaraExperts\Bolt\Concerns\HasHiddenOptions;
use LaraExperts\Bolt\Concerns\HasOptions;
use LaraExperts\Bolt\Contracts\Fields;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\FieldResponse;
use LaraExperts\Bolt\Models\Response;
// use LaraZeus\BoltPro\Models\Field as FieldPreset;

/** @phpstan-return Arrayable<string,mixed> */
abstract class FieldsContract implements Arrayable, Fields
{
    use HasHiddenOptions;
    use CustomHasOptions;
    // use HasOptions;

    public bool $disabled = false;

    public string $renderClass;

    public int $sort;

    public function toArray(): array
    {
        return [
            'disabled' => $this->disabled,
            'class' => '\\' . get_called_class(),
            'renderClass' => $this->renderClass,
            'hasOptions' => $this->hasOptions(),
            'code' => class_basename($this),
            'sort' => $this->sort,
            'title' => $this->title(),
            'description' => $this->description(),
            'icon' => $this->icon(),
        ];
    }

    public function title(): string
    {
        return __(class_basename($this));
    }

    public function description(): string
    {
        return __('field text for all the text you need');
    }

    public function icon(): string
    {
        return 'iconpark-aligntextcenter-o';
    }

    public function hasOptions(): bool
    {
        return method_exists(get_called_class(), 'getOptions');
    }

    public function getResponse(Field $field, FieldResponse $resp): string
    {
        return $resp->response;
    }

    // @phpstan-ignore-next-line
    public function appendFilamentComponentsOptions($component, $formField, bool $hasVisibility = false)
    {
        if (is_string($formField->options)) {
            $formField->options = json_decode($formField->options, true);
        }

        $htmlId = $formField->options['htmlId'] ?? str()->random(6);

        $component
            ->label($formField->name)
            ->id($htmlId)
            ->hint(function () use ($formField) {
                // if (! Bolt::hasPro()) {
                //     return null;
                // }

                // @phpstan-ignore-next-line
                // if (! $formField instanceof FieldPreset && $formField->section->form->extensions !== 'LaraZeus\\BoltPro\\Extensions\\Grades') {
                //     return null;
                // }

                return optional($formField->options)['grades']['points'] ?? 0 . ' ' . __('marks');
            })
            ->helperText($formField->description);

        if (optional($formField->options)['is_required']) {
            $component = $component->required();
        }

        if (request()->filled($htmlId)) {
            $component = $component->default(request($htmlId));
        }

        if (optional($formField->options)['column_span_full']) {
            $component = $component->columnSpanFull();
        }

        if (optional($formField->options)['hidden_label']) {
            $component = $component->hiddenLabel();
        }

        if (optional($formField->options)['hint']) {
            if (optional($formField->options)['hint']['text']) {
                $component = $component->hint($formField->options['hint']['text']);
            }
            if (optional($formField->options)['hint']['icon']) {
                $component = $component->hintIcon($formField->options['hint']['icon'], tooltip: $formField->options['hint']['icon-tooltip']);
            }
            if (optional($formField->options)['hint']['color']) {
                $component = $component->hintColor(fn () => Color::hex($formField->options['hint']['color']));
            }
        }

        $component = $component
            ->visible(function ($record, Get $get) use ($formField) {
                if (! isset($formField->options['visibility']) || ! $formField->options['visibility']['active']) {
                    return true;
                }

                $relatedField = $formField->options['visibility']['fieldID'];
                $relatedFieldValues = $formField->options['visibility']['values'];

                if (empty($relatedField) || empty($relatedFieldValues)) {
                    return true;
                }

                $relatedFieldArray = Arr::wrap($get('formData.' . $relatedField));

                // In the example where a field is only visible when the related field is NOT checked,
                // we need to convert booleans to strings for in_array comparison
                $relatedFieldArray = array_map(fn ($value) => is_bool($value) ? ($value ? 'true' : 'false') : $value, $relatedFieldArray);

                if (in_array($relatedFieldValues, $relatedFieldArray)) {
                    return true;
                }

                return false;
            });

        if ($hasVisibility) {
            return $component->live();
        }

        return $component;
    }

    public function getCollectionsValuesForResponse(Field $field, FieldResponse $resp): string
    {
        $response = $resp->response;

        if (blank($response)) {
            return '';
        }

        if (Bolt::isJson($response)) {
            $response = json_decode($response);
        }

        $response = Arr::wrap($response);

        $dataSource = (int) $field->options['dataSource'];
        $cacheKey = 'dataSource_' . $dataSource . '_response_' . md5(serialize($response));

        $response = Cache::remember($cacheKey, config('form-bolt.cache.collection_values'), function () use ($field, $response, $dataSource) {

            // Handle case when dataSource is from the default model: `Collection`
            if ($dataSource !== 0) {
                return BoltPlugin::getModel('Collection')::query()
                    ->find($dataSource)
                    ?->values
                    ->whereIn('itemKey', $response)
                    ->pluck('itemValue')
                    ->join(', ') ?? '';
            }

            // Handle case when dataSource is custom model class
            if (class_exists($field->options['dataSource'])) {
                $dataSourceClass = app($field->options['dataSource']);

                return $dataSourceClass->getQuery()
                    ->whereIn($dataSourceClass->getKeysUsing(), $response)
                    ->pluck($dataSourceClass->getValuesUsing())
                    ->join(', ');
            }

            return '';
        });

        return (is_array($response)) ? implode(', ', $response) : $response;
    }

    // @phpstan-ignore-next-line
    public static function getFieldCollectionItemsList(Field  | array $formField): Collection | array
    {
        if (is_array($formField)) {
            $formField = (object) $formField;
        }

        $getCollection = collect();

        // @phpstan-ignore-next-line
        if (optional($formField->options)['dataSource'] === null) {
            return $getCollection;
        }

        // @phpstan-ignore-next-line
        if ( is_string($formField->options)) {
            // @phpstan-ignore-next-line
            $formField->options = json_decode($formField->options, true);
        }

        // to not braking old dataSource structure
        // @phpstan-ignore-next-line
        if ((int) $formField->options['dataSource'] !== 0) {
            // @phpstan-ignore-next-line
            // if ($formField instanceof FieldPreset) {
            //     // @phpstan-ignore-next-line
            //     $getCollection = \LaraZeus\BoltPro\Models\Collection::query()
            //         // @phpstan-ignore-next-line
            //         ->find($formField->options['dataSource'] ?? 0)
            //         ->values;
            //     // @phpstan-ignore-next-line
            //     $getCollection = collect(json_decode($getCollection, true))
            //         ->pluck('itemValue', 'itemKey');
            // } else {
                $getCollection = BoltPlugin::getModel('Collection')::query()
                    ->find($formField->options['dataSource'] ?? 0);
                if ($getCollection === null) {
                    $getCollection = collect();
                } else {
                    $getCollection = $getCollection->values->pluck('itemValue', 'itemKey');
                }
            // }
        } else {
            if (class_exists($formField->options['dataSource'])) {
                // @phpstan-ignore-next-line
                $dataSourceClass = new $formField->options['dataSource'];
                $getCollection = $dataSourceClass->getQuery()->pluck(
                    $dataSourceClass->getValuesUsing(),
                    $dataSourceClass->getKeysUsing()
                );
            }
        }

        return $getCollection;
    }

    public function TableColumn(Field $field): ?Column
    {
        return TextColumn::make('formData.' . $field->id)
            ->label($field->name)
            ->sortable(false)
            ->searchable(query: function (Builder $query, string $search): Builder {
                return $query
                    ->whereHas('fieldsResponses', function ($query) use ($search) {
                        $query->where('response', 'like', '%' . $search . '%');
                    });
            })
            ->getStateUsing(fn (Response $record) => $this->getFieldResponseValue($record, $field))
            ->html()
            ->toggleable();
    }

    public function ExportColumn(Field $field): ?ExportColumn
    {
        return ExportColumn::make('formData.' . $field->options['htmlId'])
            ->label($field->name)
            ->state(function (Response $record) use ($field) {

                /** @var ?Response $response */
                $response = $record->fieldsResponses()->where('field_id', $field->id)->first();

                if ($response === null) {
                    return '-';
                }

                return $response->response;
            });
    }

    public function getFieldResponseValue(Response $record, Field $field): string
    {
        $fieldResponse = $record->fieldsResponses->where('field_id', $field->id)->first();
        if ($fieldResponse === null) {
            return '';
        }

        return (new $field->type)->getResponse($field, $fieldResponse);
    }

    public function entry(Field $field, FieldResponse $resp): string
    {
        return $this->getResponse($field, $resp);
    }
}
