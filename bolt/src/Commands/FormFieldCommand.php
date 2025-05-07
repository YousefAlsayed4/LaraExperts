<?php

namespace LaraExperts\Bolt\Commands;

use Illuminate\Console\Command;
use LaraExperts\Bolt\Concerns\CanManipulateFiles;

class FormFieldCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:form-field {plugin : filament FQN plugin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create custom field for form bolt';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filamentPluginFullNamespace = $this->argument('plugin');
        $fieldClassName = str($filamentPluginFullNamespace)->explode('\\')->last();

        $path = config('form-bolt.collectors.fields.path');
        $namespace = str_replace('\\\\', '\\', trim(config('form-bolt.collectors.fields.namespace'), '\\'));

        $this->copyStubToApp('FormField', "{$path}/{$fieldClassName}.php", [
            'namespace' => $namespace,
            'plugin' => $filamentPluginFullNamespace,
            'class' => $fieldClassName,
        ]);

        $this->info('form field created successfully!');
    }
}
