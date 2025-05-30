<?php

namespace LaraExperts\Bolt\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaraExperts\Bolt\Database\Factories\SectionFactory;
use Spatie\Translatable\HasTranslations;

/**
 * @property string $updated_at
 * @property string $name
 * @property string $columns
 * @property string $description
 * @property bool $aside
 * @property bool $borderless
 * @property bool $compact
 * @property mixed $fields
 */
class Section extends Model
{
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;

    public array $translatable = ['name'];

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    public function getTable(): string
    {
        return config('form-bolt.table-prefix') . 'sections';
    }

    protected static function booted(): void
    {
        static::deleting(function (Section $section) {
            if ($section->isForceDeleting()) {
                // @phpstan-ignore-next-line
                $section->fields()->withTrashed()->get()->each(function ($item) {
                    $item->fieldResponses()->withTrashed()->get()->each(function ($item) {
                        $item->forceDelete();
                    });
                    $item->forceDelete();
                });
            } else {
                $section->fields->each(function ($item) {
                    $item->fieldResponses->each(function ($item) {
                        $item->delete();
                    });
                    $item->delete();
                });
            }
        });
    }

    protected static function newFactory(): Factory
    {
        return SectionFactory::new();
    }

    /** @phpstan-return hasMany<Field> */
    public function fields(): HasMany
    {
        return $this->hasMany(config('form-bolt.models.Field'), 'section_id', 'id');
    }

    /** @return BelongsTo<Form, Section> */
    public function form(): BelongsTo
    {
        return $this->belongsTo(config('form-bolt.models.Form'));
    }
}
