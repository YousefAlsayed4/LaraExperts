<?php

namespace LaraExperts\Bolt\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bolt:publish {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'PublishCommand all Form and Bolt components and resources';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->callSilent('vendor:publish', ['--tag' => 'form-bolt-migrations', '--force' => $this->option('force')]);
        $this->callSilent('vendor:publish', ['--tag' => 'form-bolt-config', '--force' => $this->option('force')]);

        // publish form files
        $this->callSilent('vendor:publish', ['--tag' => 'form-config', '--force' => $this->option('force')]);
        $this->callSilent('vendor:publish', ['--tag' => 'form-views', '--force' => $this->option('force')]);
        $this->callSilent('vendor:publish', ['--tag' => 'form-assets', '--force' => $this->option('force')]);
        $this->callSilent('vendor:publish', ['--tag' => 'form-lang', '--force' => $this->option('force')]);

        $this->callSilent('vendor:publish', ['--tag' => 'filament-icon-picker-config', '--force' => $this->option('force')]);

        $this->output->success('Form and Bolt has been Published successfully');
    }
}
