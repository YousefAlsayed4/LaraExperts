<?php

namespace LaraExperts\Bolt\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'form:install';

    protected $description = 'install form';

    public function handle(): void
    {
        $this->info('publishing migrations...');
        $this->call('vendor:publish', ['--tag' => 'form-bolt-migrations']);
        $this->call('vendor:publish', ['--tag' => 'form-bolt-config']);

        $this->info('publishing assets...');
        $this->call('vendor:publish', ['--tag' => 'form-assets']);

        $this->info('installing API routes...');
        $this->call('form:install-api-routes');

        if ($this->confirm('Do you want to run the migration now?', true)) {
            $this->info('running migrations...');
            $this->call('migrate');
        }

        $this->output->success('laraExperts Bolt has been Installed successfully, consider ⭐️ the package in filament site :)');
    }
}
