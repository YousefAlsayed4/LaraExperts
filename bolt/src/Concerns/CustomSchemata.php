<?php
namespace LaraExperts\Bolt\Concerns;
use LaraExperts\Bolt\Models\Form;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use LaraExperts\Accordion\Forms\Accordion;
use LaraZeus\Accordion\Forms\Accordions;
use LaraZeus\Bolt\BoltPlugin;
use LaraZeus\Bolt\Concerns\Schemata;
use LaraZeus\Bolt\Facades\Bolt;
use Closure;
use Illuminate\Validation\Rules\Unique;
use App\Enums\LaraZusStringAndBoleanTypesEnum;
use App\Enums\LaraZusDataSourceTypesEnum;

trait CustomSchemata
{
    use schemata;

    protected static function getVisibleFields(array $sections, array $arguments): array
    {

        // @phpstan-ignore-next-line
        return collect($sections)
            ->map(function (array $sections) use ($arguments) {
                // @phpstan-ignore-next-line
                $sections['fields'] = collect($sections['fields'])
                    ->reject(function ($item, $key) use ($arguments) {
                        return $key === $arguments['item'] ||
                            ! (
                                isset($item['options']['dataSource']) ||
                                $item['type'] === '\LaraZeus\Bolt\Fields\Classes\Toggle'
                            );
                    })->all();

                return $sections;
            })->all();
    }


    protected static function sectionOptionsFormSchema(array $formOptions, array $allSections): array
    {
        return [
            TextInput::make('description')
                ->nullable()
                ->visible($formOptions['show-as'] !== 'tabs')
                ->label(__('Section Description')),

            Accordions::make('section-options')
                ->accordions(fn () => array_filter([
                    Accordion::make('visual-options')
                        ->label(__('Visual Options'))
                        ->columns()
                        ->icon('iconpark-viewgriddetail-o')
                        ->schema([
                            Select::make('columns')
                                ->options(fn (): array => array_combine(range(1, 12), range(1, 12)))
                                ->required()
                                ->default(1)
                                ->hint(__('fields per row'))
                                ->label(__('Section Columns')),
                            IconPicker::make('icon')
                                ->columns([
                                    'default' => 1,
                                    'lg' => 3,
                                    '2xl' => 5,
                                ])
                                ->label(__('Section icon')),
                            Toggle::make('aside')
                                ->default(false)
                                ->visible($formOptions['show-as'] === 'page')
                                ->label(__('show as aside')),
                            Toggle::make('compact')
                                ->default(false)
                                ->visible($formOptions['show-as'] === 'page')
                                ->label(__('compact section')),
                        ]),
                    self::visibility($allSections),
                    Bolt::getCustomSchema('section') ?? [],
                ])),
        ];
    }

    /**
     * this function make user_id saved +form tabs(form main details) to database Sections and it's details
     * @return array
     */
    public static function getMainFormSchema(): array
    {
        return [
            Hidden::make('user_id')->default(auth()->user()->id ?? null),

            Tabs::make('form-tabs')
                ->tabs(static::getTabsSchema())
                ->columnSpan(2),

            Repeater::make('sections')
                ->hiddenLabel()
                ->schema(static::getSectionsSchema())
                ->relationship()
                ->orderColumn('ordering')
                ->addActionLabel(__('Add Section'))
                ->cloneable()
                ->collapsible()
                ->collapsed(fn (string $operation) => $operation === 'edit')
                ->minItems(1)
                ->extraItemActions([
                    // @phpstan-ignore-next-line
                    Bolt::hasPro() ? \LaraZeus\BoltPro\Actions\SectionMarkAction::make('marks') : null,

                    Action::make('options')
                        ->label(__('section options'))
                        ->slideOver()
                        ->color('warning')
                        ->tooltip(__('more section options'))
                        ->icon('heroicon-m-cog')
                        ->fillForm(fn (
                            array $arguments,
                            Repeater $component
                        ) => $component->getItemState($arguments['item']))
                        ->form(function (array $arguments, Get $get) {
                            $formOptions = $get('options');

                            $allSections = $get('sections');

                            unset($allSections[$arguments['item']]);

                            $allSections = self::getVisibleFields($allSections, $arguments);

                            return static::sectionOptionsFormSchema($formOptions, $allSections);
                        })
                        ->action(function (array $data, array $arguments, Repeater $component): void {
                            $state = $component->getState();
                            $state[$arguments['item']] = array_merge($state[$arguments['item']], $data);
                            $component->state($state);
                        }),
                ])
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->columnSpan(2),
        ];
    }

    public static function getTabsSchema(): array
    {

        $tabs = [
            Tabs\Tab::make('title-slug-tab')
                ->label(__('Title & Slug'))
                ->columns()
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                       ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                           $language=app()->getLocale();

                            $form = Form::query()
                                ->where('name->'.$language,$value)
                                ->where('company_id',auth()->user()?->company_id);

                            if (!empty($form->first()) && empty($form->where('id',$get('id'))->first()))
                            {
                                $fail(__('validation.unique', ['attribute' => __('validation.form_name')]));
                            }

                                return true;

                        },
                    ])
                        ->live(onBlur: true)
                        ->validationMessages([
                            'required' => __('validation.form_name_required',['attribute' => __('validation.form_name')]),
                        ])
                        ->label(__('Form Name'))
                        ->afterStateUpdated(function (Set $set, $state, $context) {
                            if ($context === 'edit') {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->validationMessages([
                            'required' => __('validation.form_name_required',['attribute' => __('validation.slug')]),
                        ])
                        ->maxLength(255)
                        ->rules(['alpha_dash'])
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                                $form = Form::query()
                                    ->where('slug',$value)
                                    ->where('company_id',auth()->user()?->company_id);

                                if (!empty($form->first()) && empty($form->where('id',$get('id'))->first()))
                                {

                                    $fail(__('validation.unique', ['attribute' => __('validation.slug')]));

                                }

                                return true;




                            },
                        ])
                        ->label(__('Form Slug')),


                ]),

            Tabs\Tab::make('text-details-tab')
                ->label(__('Text & Details'))
                ->schema([
                    Textarea::make('description')
                        ->label(__('Form Description'))
                        ->helperText(__('shown under the title of the form and used in SEO')),
                    RichEditor::make('details')
                        ->label(__('Form Details'))
                        ->helperText(__('a highlighted section above the form, to show some instructions or more details')),
                    RichEditor::make('options.confirmation-message')
                        ->label(__('Confirmation Message'))
                        ->helperText(__('optional, show a massage whenever any one submit a new entry')),
                ]),

            Tabs\Tab::make('display-access-tab')
                ->label(__('Display & Access'))
                ->columns()
                ->schema([
                    Grid::make()
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('Is Active'))
                                ->hidden()
                                ->helperText(__('Activate the form and let users start submissions')),
                            Toggle::make('options.require-login')
                                ->label(__('require Login'))
                                ->helperText(__('User must be logged in or create an account before can submit a new entry'))
                                ->live(),
                            Toggle::make('options.one-entry-per-user')
                                ->label(__('One Entry Per User'))
                                ->helperText(__('to check if the user already submitted an entry in this form'))
                                ->visible(function (Get $get) {
                                    return $get('options.require-login');
                                }),
                        ]),
                    TextInput::make('ordering')
                        ->columnSpanFull()
                        ->numeric()
                        ->label(__('ordering'))
                        ->default(1),

                    Grid::make()
                        ->columnSpanFull()
                        ->columns(1)
                        ->schema([
                            Radio::make('options.show-as')
                                ->label(__('Show the form as'))
                                ->live()
                                ->default('page')
                                ->descriptions([
                                    'page' => __('show all sections on one page'),
                                    'wizard' => __('separate each section in steps'),
                                    'tabs' => __('Show the Form as Tabs'),
                                ])
                                ->options([
                                    'page' => __('Show on one page'),
                                    'wizard' => __('Show As Wizard'),
                                    'tabs' => __('Show As Tabs'),
                                ]),
                        ]),

                ]),

            Tabs\Tab::make('advanced-tab')
                ->label(__('Advanced'))
                ->schema([
                    Grid::make()
                        ->columns()
                        ->schema([
                            Placeholder::make('form-dates')
                                ->label(__('Form Dates'))
                                ->content(__('optional, specify when the form will be active and receiving new entries'))
                                ->columnSpanFull(),
                            DateTimePicker::make('start_date')
                                ->requiredWith('end_date')
                                ->label(__('Start Date')),
                            DateTimePicker::make('end_date')
                                ->requiredWith('start_date')
                                ->label(__('End Date')),
                        ]),
                    Grid::make()
                        ->columns()
                        ->schema([
                            TextInput::make('options.emails-notification')
                                ->label(__('Emails Notifications'))
                                ->helperText(__('optional, enter the emails (comma separated) you want to receive notification when ever you got a new entry')),
                        ]),
                ]),

            Tabs\Tab::make('extensions-tab')
                ->label(__('Extensions'))
                ->visible(BoltPlugin::get()->getExtensions() !== null)
                ->schema([
                    Select::make('extensions')
                        ->label(__('Extensions'))
                        ->preload()
                        ->live()
                        ->options(function () {
                            // @phpstan-ignore-next-line
                            return collect(BoltPlugin::get()->getExtensions())
                                ->mapWithKeys(function (string $item): array {
                                    if (class_exists($item)) {
                                        return [$item => (new $item)->label()];
                                    }

                                    return [$item => $item];
                                });
                        }),
                ]),

            Tabs\Tab::make('design')
                ->label(__('Design'))
                ->visible(Bolt::hasPro() && config('zeus-bolt.allow_design'))
                ->schema([
                    ViewField::make('options.primary_color')
                        ->hiddenLabel()
                        ->view('zeus::filament.components.color-picker'),
                    FileUpload::make('options.logo')
                        ->disk(config('zeus-bolt.uploadDisk'))
                        ->directory(config('zeus-bolt.uploadDirectory'))
                        ->visibility(config('zeus-bolt.uploadVisibility'))
                        ->image()
                        ->imageEditor()
                        ->label(__('Logo')),
                    FileUpload::make('options.cover')
                        ->disk(config('zeus-bolt.uploadDisk'))
                        ->directory(config('zeus-bolt.uploadDirectory'))
                        ->visibility(config('zeus-bolt.uploadVisibility'))
                        ->image()
                        ->imageEditor()
                        ->label(__('Cover')),
                ]),
        ];

        $customSchema = Bolt::getCustomSchema('form');

        if ($customSchema !== null) {
            $tabs[] = $customSchema;
        }

        return $tabs;
    }

    //section name and fields
    public static function getSectionsSchema(): array
    {
        return array_filter([
            TextInput::make('name')
                ->columnSpanFull()
                ->required()
                ->validationMessages([
                    'required' => __('validation.form_name_required',['attribute' => __('validation.section_name')]),
                ])
                ->lazy()
                ->label(__('Section Name')),

            Placeholder::make('section-fields-placeholder')
                ->label(__('Section Fields')),

            Repeater::make('fields')
                ->relationship()
                ->orderColumn('ordering')
                ->cloneable()
                ->minItems(1)
                ->cloneAction(fn (Action $action) => $action->action(function (Component $component, $arguments) {
                    $items = $component->getState();
                    $originalItem = $items[$arguments['item']];
                    $clonedItem = array_merge($originalItem, [
                        'name' => $originalItem['name'] . ' new',
                        'options' => array_merge($originalItem['options'], [
                            'htmlId' => $originalItem['options']['htmlId'] . Str::random(2),
                        ]),
                    ]);

                    $items[] = $clonedItem;
                    $component->state($items);

                    return $items;
                }))
                ->collapsible()
                ->collapsed(fn (string $operation) => $operation === 'edit')
                ->grid([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 3,
                    '2xl' => 3,
                ])
                ->label('')
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->addActionLabel(__('Add field'))
                ->extraItemActions([
                    // @phpstan-ignore-next-line
                    Bolt::hasPro() ? \LaraZeus\BoltPro\Actions\FieldMarkAction::make('marks') : null,

                    Action::make('fields options')
                        ->slideOver()
                        ->color('warning')
                        ->tooltip('more field options')
                        ->icon('heroicon-m-cog')
                        ->modalIcon('heroicon-m-cog')
                        ->modalDescription(__('advanced fields settings'))
                        ->fillForm(
                            fn (array $arguments, Repeater $component) => $component->getItemState($arguments['item'])
                        )
                        ->form(function (Get $get, array $arguments, Repeater $component) {

                            $allSections = self::getVisibleFields($get('../../sections'), $arguments);

                            return [
                                Textarea::make('description')
                                    ->label(__('Field Description')),
                                Group::make()
                                    ->label(__('Field Options'))
                                    ->schema(function (Get $get) use ($allSections, $component, $arguments) {

                                        $class=  Str::replaceFirst('\LaraZeus\Bolt\Fields\Classes', 'App\Filament\CompanyAdmin\Bolt\Fields\Classes', $get('type'));

                                        if (class_exists($class)) {

                                            $newClass = (new $class);
                                            if ($newClass->hasOptions()) {
                                                return $newClass->getOptions($allSections, $component->getState()[$arguments['item']]);
                                            }
                                        }

                                        return [];
                                    }),
                            ];
                        })
                        ->action(function (array $data, array $arguments, Repeater $component): void {

                            $state = $component->getState();
                            $state[$arguments['item']] = array_merge($state[$arguments['item']], $data);
                            $component->state($state);
                        }),
                ])
                ->validationMessages([
                    'min'=>__('validation.form_sections_required')
                ])
                ->schema(static::getFieldsSchema()),

            Hidden::make('compact')->default(0)->nullable(),
            Hidden::make('aside')->default(0)->nullable(),
            Hidden::make('icon')->nullable(),
            Hidden::make('columns')->default(1)->nullable(),
            Hidden::make('description')->nullable(),
            Hidden::make('options.visibility.active')->default(0)->nullable(),
            Hidden::make('options.visibility.fieldID')->nullable(),
            Hidden::make('options.visibility.values')->nullable(),
            ...Bolt::getHiddenCustomSchema('section') ?? [],
        ]);
    }

    public static function getCleanOptionString(array $field): string
    {
        return
            view('zeus::filament.fields.types')
                ->with('field', $field)
                ->render();
    }

    public static function getFieldsSchema(): array
    {
        $supportedFields = array_merge(
            LaraZusStringAndBoleanTypesEnum::toValues(),
            LaraZusDataSourceTypesEnum::toValues()
        );
        return [
            Hidden::make('description'),
            TextInput::make('name')
                ->required()
                ->validationMessages([
                    'required' => __('validation.form_name_required',['attribute' => __('validation.field_name')]),
                ])
                ->lazy()
                ->label(__('Field Name')),
            Select::make('type')
                ->required()
                ->validationMessages([
                    'required' => __('validation.field_type_required',['attribute' => __('validation.field_type')]),
                ])
                ->searchable()
                ->preload()
                ->getSearchResultsUsing(function (string $search) use ($supportedFields) {

                    return Bolt::availableFields()
                        ->whereIn('class', $supportedFields)
                        ->filter(fn ($q) => str($q['title'])->contains($search, ignoreCase: true))
                        ->mapWithKeys(fn ($field) => [$field['class'] => static::getCleanOptionString($field)])
                        ->toArray();
                })
                ->allowHtml()
                ->extraAttributes(['class' => 'field-type'])
                ->options(function () use ($supportedFields) {
                    return Bolt::availableFields()
                        ->whereIn('class', $supportedFields)
                        ->mapWithKeys(function ($field) {
                            return [$field['class'] => static::getCleanOptionString($field)];
                        })
                        ->toArray();
                })
                ->live()
                ->default('\LaraZeus\Bolt\Fields\Classes\TextInput')
                ->label(__('Field Type')),
            Group::make()
                ->schema(function (Get $get) {
                  $class=  Str::replaceFirst('\LaraZeus\Bolt\Fields\Classes', 'App\Filament\CompanyAdmin\Bolt\Fields\Classes', $get('type'));

                  if (class_exists($class)) {
                        $newClass = (new $class);
                        if ($newClass->hasOptions()) {
                            // @phpstan-ignore-next-line
                            return collect($newClass->getOptionsHidden())->flatten()->toArray();
                        }
                    }

                    return [];
                }),
        ];
    }
}
