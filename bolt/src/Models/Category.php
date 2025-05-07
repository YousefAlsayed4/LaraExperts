<?php

namespace LaraExperts\Bolt\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use LaraExperts\Bolt\Concerns\HasUpdates;
use LaraExperts\Bolt\Database\Factories\CategoryFactory;
use Spatie\Translatable\HasTranslations;

/**
 * @property string $updated_at
 * @property string $name
 * @property string $logo
 */
class Category extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasUpdates;
    use SoftDeletes;

    public array $translatable = ['name', 'description'];

    protected $guarded = [];

    public function getTable()
    {
        return config('form-bolt.table-prefix') . 'categories';
    }

    protected static function newFactory(): Factory
    {
        return CategoryFactory::new();
    }

    /** @return HasMany<Form> */
    public function forms(): HasMany
    {
        return $this->hasMany(config('form-bolt.models.Form'));
    }

    /**
     * @return Attribute<string, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::disk(config('form-bolt.uploadDisk'))->url($this->logo),
        );
    }
}
