<?php

namespace LaraExperts\Bolt;

use LaraExperts\Bolt\Commands\InstallCommand;
use LaraExperts\Bolt\Commands\PublishCommand;
use LaraExperts\Bolt\Commands\FormDatasourceCommand;
use LaraExperts\Bolt\Commands\FormFieldCommand;
use LaraExperts\Bolt\Commands\InstallApiRoutesCommand;
use LaraExperts\Bolt\Commands\MakeFormControllerCommand;
use LaraExperts\Bolt\Commands\MakeImageSupport;
use LaraExperts\Bolt\Livewire\FillForms;
use LaraExperts\Bolt\Livewire\ListEntries;
use LaraExperts\Bolt\Livewire\ListForms;
use LaraExperts\Core\CoreServiceProvider;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BoltServiceProvider extends PackageServiceProvider
{
    public static string $name = 'form-bolt';

   public function configurePackage(Package $package): void
{
    $package
        ->name(static::$name)
        ->hasViews('form')
        ->hasMigrations($this->getMigrations())
        ->hasTranslations()
        ->hasConfigFile('form-bolt') // Change to 'form-bolt'
        ->hasCommands($this->getCommands())
        ->hasRoute('web');
}

    public function packageBooted(): void
    {
        CoreServiceProvider::setThemePath('bolt');

        Livewire::component('bolt.fill-form', FillForms::class);
        Livewire::component('bolt.list-forms', ListForms::class);
        Livewire::component('bolt.list-entries', ListEntries::class);
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            PublishCommand::class,
            FormFieldCommand::class,
            FormDatasourceCommand::class,
            InstallCommand::class,
            InstallApiRoutesCommand::class,
            MakeFormControllerCommand::class,
            MakeImageSupport::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_categories_table',
            'create_collections_table',
            'create_forms_table',
            'create_sections_table',
            'create_fields_table',
            'create_responses_table',
            'create_field_responses_table',
            'add_extensions_to_forms',
            'add_extension_item_responses',
            'alter_tables_constraints',
            'add_compact_to_section',
            'add_options_to_section',
            'add_grade_to_response',
            'add_grade_to_field_response',
            'add_borderless_to_section',
        ];
    }
}
