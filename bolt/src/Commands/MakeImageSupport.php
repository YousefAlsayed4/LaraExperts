<?php 

namespace LaraExperts\Bolt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeImageSupport extends Command
{
    protected $signature = 'make:image-support';
    protected $description = 'Set up ImageController, migration, and User model media collections';

    public function handle()
    {
        // 1. Create Images folder in Controllers
        $controllerDir = app_path('Http/Controllers/Images');
        if (!File::exists($controllerDir)) {
            File::makeDirectory($controllerDir, 0755, true);
            $this->info("Created folder: $controllerDir");
        }

        // 2. Create ImageController.php
        $controllerPath = "$controllerDir/ImageController.php";
        File::put($controllerPath, $this->getImageControllerContent());
        $this->info("Created: ImageController.php");

        // 3. Create media migration file
        $timestamp = date('Y_m_d_His');
        $migrationName = "{$timestamp}_create_media_table.php";
        $migrationPath = database_path("migrations/$migrationName");
        File::put($migrationPath, $this->getMediaMigrationContent());
        $this->info("Created migration: $migrationName");

        // 4. Append code to User.php model
        $userModel = app_path('Models/User.php');
        if (File::exists($userModel)) {
            $userContent = File::get($userModel);
           if (!Str::contains($userContent, '// === MEDIA COLLECTIONS ADDED BY make:image-support COMMAND ===')) {
            $injectedCode = $this->getUserModelMediaCode();
            $userContent = preg_replace('/}\s*$/', $injectedCode . "\n}", $userContent);
            File::put($userModel, $userContent);
            $this->info("Updated: User model with media collection code.");
        } else {
                $this->warn("User model already contains media logic.");
        }
        } else {
            $this->error("User model not found.");
        }

        $this->info('✅ Image support setup completed.');
    }

    protected function getImageControllerContent(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Images;

use LaraExperts\Bolt\Filament\Enums\FormFileTypes;
use LaraExperts\Bolt\Filament\Enums\Http;
use LaraExperts\Bolt\Filament\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use LaraExperts\Bolt\Filament\Requests\Api\Images\UploadImageRequest;
use LaraExperts\Bolt\Filament\Resources\Api\ImageResource;
use App\Models\FieldResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageController extends Controller
{
    public function upload(UploadImageRequest $request):APIResponse
    {
        /** @var \Illuminate\Http\Request $request */
        $files = $request->allFiles()['files'];

        $images=[];
        foreach ($files as $file) {
            if ($request->file_type == FormFileTypes::IMAGE->value && $file->getMimeType()!="image/svg+xml") {
                $images[] = $this->compress_image($file);
            } else {
                $images[] = auth()->user()->addMedia($file)->toMediaCollection(User::$user_images);
            }
        }

        return new APIResponse(
            status:"success",
            code:Http::OK,
            body:[
               'image'=>  ImageResource::collection($images)
            ],
        );
    }

    /**
     * compress images
     * @param $file
     * @return Media
     */
    private function compress_image($file)
    {
        $image_name = $file->getClientOriginalName();
        $manager = new ImageManager(new Driver());
        $compressed_image = $manager->read($file)->save('storage/'.$image_name, quality: 10);
        $image = auth()->user()->addMedia('storage/'.$image_name)->toMediaCollection(User::$user_images);
        $filePath = $file->storeAs("/original_images/", 'original_'.$image_name.'-' . $image->id . '.' . $file->getClientOriginalExtension());
        return $image;
    }
}
PHP;
    }

  protected function getUserModelMediaCode(): string
{
    return <<<'PHP'


    // === MEDIA COLLECTIONS ADDED BY make:image-support COMMAND ===
    public static $user_images = 'user_images';
    public static $field_response_image = 'field_response_image';

    public function registerPrimaryMediaCollection(): void
    {
        $this
            ->addMediaCollection(self::$user_images)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'image/svg']);
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(config('media-library.media_model'), 'model')
            ->where('collection_name', self::$user_images);
    }

PHP;
}


    protected function getMediaMigrationContent(): string
{
    return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable();
            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
PHP;
}

}
