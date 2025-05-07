<?php

namespace LaraExperts\Bolt\Concerns;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Facades\Extensions;
use LaraExperts\Bolt\Models\Form; // Use the correct class
use LaraExperts\Bolt\Models\Section as FormSection;

trait Designer
{
    public static function ui(Form $Form, bool $inline = false): array
    {
        $sections = self::drawExt($Form);
        $hasSectionVisibility = $Form->sections->pluck('options')->where('visibility.active', true)->isNotEmpty();

        foreach ($Form->sections->sortBy('ordering') as $section) {
            $sections[] = self::drawSections(
                $Form,
                $section,
                self::drawFields($section, $inline, $hasSectionVisibility),
            );
        }

        if (optional($Form->options)['show-as'] === 'tabs') {
            return [Tabs::make('tabs')->live(condition: $hasSectionVisibility)->tabs($sections)];
        }

        if (optional($Form->options)['show-as'] === 'wizard') {
            return [
                Wizard::make($sections)
                    ->live(condition: $hasSectionVisibility),
                // ->skippable() // todo still not working
            ];
        }

        return $sections;
    }

    private static function drawExt(Form $Form): array
    {
        $getExtComponent = Extensions::init($Form, 'formComponents');

        if ($getExtComponent === null) {
            return [];
        }

        return [
            Section::make('extensions')
                ->heading(function () use ($Form) {
                    $class = $Form->extensions;
                    if (class_exists($class)) {
                        return (new $class)->label();
                    }

                    return __('Extension');
                })
                ->schema($getExtComponent),
        ];
    }

    private static function drawFields(FormSection $section, bool $inline, bool $hasSectionVisibility = false): array
    {
        $hasVisibility = $hasSectionVisibility || $section->fields->pluck('options')->where('visibility.active', true)->isNotEmpty();

        $fields = [];

        if (! $inline) {
            $fields[] = Bolt::renderHook('lara-form-section.before');
        }

        foreach ($section->fields->sortBy('ordering') as $formField) {
            if (! $inline) {
                $fields[] = Bolt::renderHook('lara-form-field.before');
            }

            $fieldClass = new $formField->type;
            $component = $fieldClass->renderClass::make('formData.' . $formField->id);

            $fields[] = $fieldClass->appendFilamentComponentsOptions($component, $formField, $hasVisibility);

            if (! $inline) {
                $fields[] = Bolt::renderHook('lara-form-field.after');
            }
        }

        if (! $inline) {
            $fields[] = Bolt::renderHook('lara-form-section.after');
        }

        return $fields;
    }

    private static function drawSections(Form $Form, FormSection $section, array $fields): Tab | Step | Section | Grid
    {
        if (optional($Form->options)['show-as'] === 'tabs') {
            $component = Tab::make($section->name)
                ->icon($section->icon ?? null);
        } elseif (optional($Form->options)['show-as'] === 'wizard') {
            $component = Step::make($section->name)
                ->description($section->description)
                ->icon($section->icon ?? null);
        } elseif ((bool) $section->borderless === true) {
            $component = Grid::make($section->name);
        } else {
            $component = Section::make($section->name)
                ->description($section->description)
                ->aside(fn () => $section->aside)
                ->compact(fn () => $section->compact)
                ->icon($section->icon ?? null)
                ->collapsible();
        }

        $component->visible(function ($record, Get $get) use ($section) {

            if (! isset($section->options['visibility']) || ! $section->options['visibility']['active']) {
                return true;
            }

            $relatedField = $section->options['visibility']['fieldID'];
            $relatedFieldValues = $section->options['visibility']['values'];

            if (empty($relatedField) || empty($relatedFieldValues)) {
                return true;
            }

            if (is_array($get('formData.' . $relatedField))) {
                return in_array($relatedFieldValues, $get('formData.' . $relatedField));
            }

            return $relatedFieldValues == $get('formData.' . $relatedField);
        });

        return $component
            ->schema($fields)
            ->columns($section->columns);
    }
}
