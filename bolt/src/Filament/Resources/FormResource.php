<?php

namespace LaraExperts\Bolt\Filament\Resources;

use Barryvdh\Debugbar\Facades\Debugbar;
use Closure;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Navigation\NavigationItem;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraExperts\Bolt\BoltPlugin;
use LaraExperts\Bolt\Concerns\HasOptions;
use LaraExperts\Bolt\Concerns\Schemata;
use LaraExperts\Bolt\Facades\Bolt;
use LaraExperts\Bolt\Filament\Actions\ReplicateFormAction;
use LaraExperts\Bolt\Filament\Resources\FormResource\Pages;
use LaraExperts\Bolt\Models\Form as ExpertsForm;

// use LaraExperts\ListGroup\Infolists\ListEntry;

class FormResource extends BoltResource
{
    use HasOptions;
    use Schemata;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static Closure | array | null $boltFormSchema = null;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;



    public static function getModel(): string
    {
        return BoltPlugin::getModel('Form');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! BoltPlugin::getNavigationBadgesVisibility(self::class)) {
            return null;
        }

        return (string) BoltPlugin::getModel('Form')::query()->count();
    }

    public static function getModelLabel(): string
    {
        return __('Form');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Forms');
    }

    public static function getNavigationItems(): array
    {
        $formsQuery = ExpertsForm::query()->latest()->take(3);
        $forms = $formsQuery->get();
        $formsCount = $formsQuery->count();
    
        $navigationGroup = __('Forms');
    
        $navigationItems = $forms->map(function ($form) use ($navigationGroup) {
            return NavigationItem::make()
                ->label($form->name)
                ->url(route('filament.admin.resources.forms.view', ['record' => $form->slug]))
                ->icon('heroicon-o-folder-minus')
                ->group($navigationGroup);
        })->toArray();
    
        // Add "See More" if forms exist
        if ($formsCount > 0) {
            $navigationItems[] = NavigationItem::make()
                ->label(__('See More'))
                ->url(route('filament.admin.resources.forms.index'))
                ->icon('heroicon-o-eye')
                ->group($navigationGroup);
        }
    
        // If no forms exist, show "List Forms"
        if ($formsCount == 0) {
            $navigationItems[] = NavigationItem::make()
                ->label(__('List Forms'))
                ->url(route('filament.admin.resources.forms.index'))
                ->icon('heroicon-o-clipboard-list')
                ->group($navigationGroup);
        }
    
        // Always show "Create Form"
        $navigationItems[] = NavigationItem::make()
            ->label(__('Create Form'))
            ->url(route('filament.admin.resources.forms.create'))
            ->icon('heroicon-o-plus-circle')
            ->group($navigationGroup);
    
        return $navigationItems;
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()->schema([
                    TextEntry::make('name')
                        ->label(__('name')),

                    // ListEntry::make('items')
                    //     ->visible(fn (ExpertsForm $record) => $record->extensions !== null)
                    //     ->heading(__('Form Links'))
                    //     ->list()
                    //     ->state(fn ($record) => $record->slug_url),

                    TextEntry::make('slug')
                        ->label(__('slug'))
                        ->url(fn (ExpertsForm $record) => route(BoltPlugin::get()->getRouteNamePrefix() . 'bolt.form.show', ['slug' => $record->slug]))
                        ->visible(fn (ExpertsForm $record) => $record->extensions === null)
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->openUrlInNewTab(),

                    TextEntry::make('description')
                        ->label(__('description')),
                    IconEntry::make('is_active')
                        ->label(__('is active'))
                        ->icon(fn (string $state): string => match ($state) {
                            '0' => 'heroicon-o-document-text',
                            default => 'heroicon-o-document-text',
                        })
                        ->color(fn (string $state): string => match ($state) {
                            '0' => 'warning',
                            '1' => 'success',
                            default => 'gray',
                        }),

                    TextEntry::make('start_date')
                        ->label(__('start date'))
                        ->dateTime(),
                    TextEntry::make('end_date')
                        ->label(__('end date'))
                        ->dateTime(),
                ])
                    ->columns(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::$boltFormSchema ?? static::getMainFormSchema());
    }

    public function getBoltFormSchema(): array | Closure | null
    {
        return static::$boltFormSchema;
    }

    public static function getBoltFormSchemaUsing(array | Closure | null $form): void
    {
        static::$boltFormSchema = $form;
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('ordering')
            ->columns([
                TextColumn::make('id')->sortable()->label(__('Form ID'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')->searchable()->sortable()->label(__('Form Name'))->toggleable(),
                IconColumn::make('is_active')->boolean()->label(__('Is Active'))->sortable()->toggleable(),
                TextColumn::make('start_date')->dateTime()->searchable()->sortable()->label(__('Start Date'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('end_date')->dateTime()->searchable()->sortable()->label(__('End Date'))->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('responses_exists')->boolean()->exists('responses')->label(__('Responses Exists'))->sortable()->toggleable()->searchable(false),
                TextColumn::make('responses_count')->counts('responses')->label(__('Responses Count'))->sortable()->toggleable()->searchable(false),
            ])
            ->actions(static::getActions())
            ->filters([
                TrashedFilter::make(),
                Filter::make('is_active')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label(__('Is Active')),

                Filter::make('not_active')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false))
                    ->label(__('Inactive')),

            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                ForceDeleteBulkAction::make(),
                RestoreBulkAction::make(),
            ]);
    }

    /** @phpstan-return Builder<ExpertsForm> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        $pages = [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
            'view' => Pages\ViewForm::route('/{record}'),
            'report' => Pages\ManageResponses::route('/{record}/report'),
            // 'browse' => Pages\BrowseResponses::route('/{record}/browse'),
            'viewResponse' => Pages\ViewResponse::route('/{record}/response/{responseID}'),
        ];

        // if (Bolt::hasPro()) {
        //     // @phpstan-ignore-next-line
        //     $pages['prefilled'] = \LaraZeus\BoltPro\Livewire\PrefilledForm::route('/{record}/prefilled');
        //     // @phpstan-ignore-next-line
        //     $pages['share'] = \LaraZeus\BoltPro\Livewire\ShareForm::route('/{record}/share');
        // }

        return $pages;
    }

    public static function getWidgets(): array
    {
        $widgets = [
            FormResource\Widgets\FormOverview::class,
            // FormResource\Widgets\ResponsesPerMonth::class,
            // FormResource\Widgets\ResponsesPerStatus::class,
            // FormResource\Widgets\ResponsesPerFields::class,
        ];

        // if (Bolt::hasPro()) {
        //     // @phpstan-ignore-next-line
        //     $widgets[] = \LaraZeus\BoltPro\Widgets\ResponsesPerCollection::class;
        // }

        return $widgets;
    }

    

    public static function getActions(): array
    {
        $action = [
            ViewAction::make(),
            EditAction::make('edit'),
            ReplicateFormAction::make(),
            RestoreAction::make(),
            DeleteAction::make()
                ->visible(fn (ExpertsForm $record): bool => ! $record->is_active && $record->responses()->count() === 0),
            ForceDeleteAction::make(),

            ActionGroup::make([
                Action::make('entries')
                    ->color('warning')
                    ->label(__('Entries'))
                    ->icon('heroicon-o-document-text')
                    ->tooltip(__('view all entries'))
                    // ->url(fn (ExpertsForm $record): string => FormResource::getUrl('report', ['record' => $record])),
            ])
                ->dropdown(false),
        ];

        $advancedActions = $moreActions = [];

        // if (Bolt::hasPro()) {
        //     $advancedActions[] = Action::make('prefilledLink')
        //         ->label(__('Prefilled Link'))
        //         ->icon('iconpark-formone-o')
        //         ->tooltip(__('Get Prefilled Link'));
        //         // ->visible(Bolt::hasPro());
        //         // ->url(fn (ExpertsForm $record): string => FormResource::getUrl('prefilled', ['record' => $record]));
        // }

        // if (class_exists(\LaraZeus\Helen\HelenServiceProvider::class)) {
        //     // @phpstan-ignore-next-line
        //     $advancedActions[] = \LaraZeus\Helen\Actions\ShortUrlAction::make('get-link')
        //         ->label(__('Short Link'))
        //         ->distUrl(fn (ExpertsForm $record) => route(BoltPlugin::get()->getRouteNamePrefix() . 'bolt.form.show', $record));
        // }

        $moreActions[] = ActionGroup::make($advancedActions)->dropdown(false);

        return [ActionGroup::make(array_merge($action, $moreActions))];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $formNavs = [
            Pages\ViewForm::class,
            Pages\EditForm::class,
        ];

        // if (Bolt::hasPro()) {
        //     // @phpstan-ignore-next-line
        //     $formNavs[] = \LaraZeus\BoltPro\Livewire\ShareForm::class;
        // }

        $respNavs = [
            Pages\ManageResponses::class,
            // Pages\BrowseResponses::class,
        ];

        return $page->generateNavigationItems([
            ...$formNavs,
            ...$respNavs,
        ]);
    }
}