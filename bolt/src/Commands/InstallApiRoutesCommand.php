<?php

namespace LaraExperts\Bolt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallApiRoutesCommand extends Command
{
    protected $signature = 'form:install-api-routes';
    protected $description = 'Install form API routes to routes/api.php';

    public function handle(): void
    {
        $apiRoutesPath = base_path('routes/api.php');
        $routesContent = <<<'EOD'

use App\Http\Controllers\Api\Form\FormController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('company')->group(function () {
    Route::get('/active_form', [FormController::class, 'get_all_active_forms']);
    Route::post('/submit_form', [FormController::class, 'submit_form']);
});

EOD;

        // Check if routes file exists
        if (!File::exists($apiRoutesPath)) {
            File::put($apiRoutesPath, '<?php'.PHP_EOL.PHP_EOL);
        }

        // Get current content
        $currentContent = File::get($apiRoutesPath);

        // Check if routes already exist
        if (str_contains($currentContent, 'FormController')) {
            $this->error('Form API routes already exist in api.php');
            return;
        }

        // Append new routes
        File::append($apiRoutesPath, PHP_EOL.$routesContent.PHP_EOL);

        $this->info('Successfully added form API routes to routes/api.php');
    }
}