<?php

namespace LaraExperts\Bolt\Commands;

use Illuminate\Console\Command;
use LaraExperts\Bolt\Concerns\CanManipulateFiles;

class FormDatasourceCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:form-datasource {name : Datasource Name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create custom Datasource for form bolt';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filamentPluginFullNamespace = $this->argument('name');

        $path = config('form-bolt.collectors.dataSources.path');
        $namespace = str_replace('\\\\', '\\', trim(config('form-bolt.collectors.dataSources.namespace'), '\\'));

        $this->copyStubToApp('FormDataSources', "{$path}/{$filamentPluginFullNamespace}.php", [
            'namespace' => $namespace,
            'class' => $filamentPluginFullNamespace,
        ]);

        $this->info('form datasource created successfully!');
    }
}
