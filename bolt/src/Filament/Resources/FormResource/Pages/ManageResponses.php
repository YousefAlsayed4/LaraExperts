<?php

namespace LaraExperts\Bolt\Filament\Resources\FormResource\Pages;

use LaraExperts\Bolt\Filament\Resources\FormResource;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraExperts\Bolt\Filament\Actions\SetResponseStatus;
use LaraExperts\Bolt\Filament\Exports\ResponseExporter;
use LaraExperts\Bolt\Models\Field;
use LaraExperts\Bolt\Models\Form;
use LaraExperts\Bolt\Models\Response;
use Filament\Tables\View\TablesRenderHook;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;

class ManageResponses extends ManageRelatedRecords
{
    protected static string $resource = FormResource::class;
    protected static string $relationship = 'responses';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    public $activeFilterNames = [];

    public function mount($record): void
{
    parent::mount($record);

    // Debug: Print all filter names
    $allFilterNames = $this->getAllFilterNames();
    // dump('All Filter Names:', $allFilterNames);

    // Initialize activeFilterNames from session or default to all filter names
    $this->activeFilterNames = session()->get("form_{$this->record->id}_active_filters", $allFilterNames);

    // Debug: Print active filter names
    // dump('Active Filter Names:', $this->activeFilterNames);
}
    
    protected function getAllFilterNames(): array
    {
        // Get all filter names dynamically
        $filters = $this->getTableFilters();
        return array_map(fn($filter) => $filter->getName(), $filters);
    }
    public function table(Table $table): Table
    {
        $userModel = \LaraExperts\Bolt\BoltPlugin::getModel('User') ?? config('auth.providers.users.model');
        $getUserModel = $userModel::getBoltUserFullNameAttribute();

        $mainColumns = [
            ImageColumn::make('user.avatar')
                ->sortable(false)
                ->searchable(false)
                ->label(__('Avatar'))
                ->circular()
                ->toggleable(),

            TextColumn::make('user.' . $getUserModel)
                ->label(__('Name'))
                ->toggleable()
                ->sortable()
                ->default(__('guest'))
                ->searchable(),

            TextColumn::make('status')
                ->toggleable()
                ->sortable()
                ->badge()
                ->label(__('status'))
                ->formatStateUsing(fn($state) => __(str($state)->title()->toString()))
                ->grow(false)
                ->searchable('status'),

            TextColumn::make('notes')
                ->label(__('notes'))
                ->sortable()
                ->searchable()
                ->toggleable(),
        ];

        foreach ($this->record->fields->sortBy('ordering') as $field) {
            $getFieldTableColumn = (new $field->type)->TableColumn($field);
            
            if ($getFieldTableColumn !== null) {
                $getFieldTableColumn->name('field_' . $field->id);
                $mainColumns[] = $getFieldTableColumn;
            }
        }

        $mainColumns[] = TextColumn::make('created_at')
            ->sortable()
            ->searchable()
            ->dateTime()
            ->label(__('created at'))
            ->toggleable();

            $filters = [
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label(__('Status')),
            ];
        
            // Add dynamic field filters
            foreach ($this->record->fields->sortBy('ordering') as $field) {
                $fieldId = $field->id;
            
                $filters[] = SelectFilter::make($field->name)
                    ->optionsLimit(3)
                    ->searchable()
                    ->options(
                        \LaraExperts\Bolt\BoltPlugin::getModel('Response')::query()
                            ->where('form_id', $this->record->id)
                            ->whereHas('fieldsResponses', fn($query) => $query->where('field_id', $fieldId))
                            ->with(['fieldsResponses' => fn($query) => $query->where('field_id', $fieldId)])
                            ->get()
                            ->flatMap(fn($response) => $response->fieldsResponses
                                ->where('field_id', $fieldId)
                                ->pluck('response'))
                            ->unique()
                            ->values()
                            ->mapWithKeys(fn($item) => [$item => $item])
                            ->toArray()
                    )
                    ->label($field->name)
                    ->name('field_' . $fieldId) // Ensure consistent naming
                    ->query(function ($query, $data) use ($fieldId) {
                        if (!empty($data['value'])) {
                            return $query->whereHas('fieldsResponses', fn($query) => 
                                $query->where('field_id', $fieldId)
                                    ->where('response', $data['value'])
                            );
                        }
                        return $query;
                    });
            }
            return $table
                ->query(
                    \LaraExperts\Bolt\BoltPlugin::getModel('Response')::query()
                        ->where('form_id', $this->record->id)
                        ->with(['fieldsResponses'])
                        ->withoutGlobalScopes([SoftDeletingScope::class])
                )
                ->columns($mainColumns)
                ->actions([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
                ->bulkActions([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                        ->label(__('Export Responses'))
                        ->exporter(ResponseExporter::class),
                ])
                ->recordUrl(
                    fn(Response $record): string => FormResource::getUrl('viewResponse', [
                        'record' => $record->form->slug,
                        'responseID' => $record,
                    ])
                )
                ->toggleColumnsTriggerAction(function ($action) use ($filters) {
                    $action->after(function () use ($filters) {
                        $visibleColumns = $this->getTable()->getVisibleColumns();
                        $activeFilters = $this->filtersToggling($filters, \Arr::dot($visibleColumns));
                        $activeFilterNames = array_map(fn($filter) => $filter->getName(), $activeFilters);
                        session()->put("form_{$this->record->id}_active_filters", $activeFilterNames);
                        $this->activeFilterNames = $activeFilterNames;
                    });
                    return $action;
                })
                ->filters($this->getActiveFilters($filters))
                ->filtersTriggerAction(function ($action) use ($filters) {
                    $this->registerTableRenderHook($filters);
                    return $action;
                });
        }
        
        

    public static function getNavigationLabel(): string
    {
        return __('Entries Report');
    }

    public function getTitle(): string
    {
        return __('Entries Report');
    }

    public function filtersToggling(array $filters, array $visibleColumns): array
    {
        $alwaysIncludedFilters = ['created_at', 'trashed', 'status'];

        return array_filter($filters, function ($filter) use ($visibleColumns, $alwaysIncludedFilters) {
            $filterName = $filter->getName();

            if (in_array($filterName, $alwaysIncludedFilters)) {
                return true;
            }

            if (str_contains($filterName, '.')) {
                $fieldId = substr($filterName, strpos($filterName, '.') + 1);
                return isset($visibleColumns["field_{$fieldId}"]) && $visibleColumns["field_{$fieldId}"];
            }

            return true;
        });
    }

    public function getActiveFilters(array $filters): array
{
    $activeFilterNames = $this->activeFilterNames;
    
    if (empty($activeFilterNames)) {
        return $filters;
    }

    return array_filter($filters, fn($filter) => in_array($filter->getName(), $activeFilterNames));
}

    public function registerTableRenderHook($filters): void
    {
        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_TOGGLE_COLUMN_TRIGGER_AFTER,
            fn(): string => view('form::filament.fields.filter-toggle', [
                'activeFilterNames' => $this->activeFilterNames,
                'filters' => $filters,
            ])->render()
        );
    }

    public function toggleFilter(string $filterName): void
    {
        $activeFilterNames = $this->activeFilterNames;
        
        if (in_array($filterName, $activeFilterNames)) {
            // If the filter is already active, remove it
            $activeFilterNames = array_diff($activeFilterNames, [$filterName]);
        } else {
            // If the filter is not active, add it
            $activeFilterNames[] = $filterName;
        }
    
        // Store the updated active filters in the session
        session()->put("form_{$this->record->id}_active_filters", $activeFilterNames);
    
        // Update the component's state
        $this->activeFilterNames = $activeFilterNames;
    
        // Reset the table to apply the updated filters
        $this->resetTable();
    }
}