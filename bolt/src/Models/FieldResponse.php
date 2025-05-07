<?php

namespace LaraExperts\Bolt\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaraExperts\Bolt\Database\Factories\FieldResponseFactory;

/**
 * @property string $response
 * @property string $updated_at
 * @property int $field_id
 * @property int $form_id
 * @property int $grade
 */
class FieldResponse extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $with = ['field'];

    protected $guarded = [];

    public function getTable()
    {
        return config('form-bolt.table-prefix') . 'field_responses';
    }

    protected static function newFactory(): Factory
    {
        return FieldResponseFactory::new();
    }

    /** @return BelongsTo<Field, FieldResponse> */
    public function field(): BelongsTo
    {
        return $this->belongsTo(config('form-bolt.models.Field'));
    }

    /** @return BelongsTo<Response, FieldResponse> */
    public function parentResponse()
    {
        return $this->belongsTo(config('form-bolt.models.Response'), 'response_id', 'id');
    }

    /** @return BelongsTo<Form, FieldResponse> */
    public function form()
    {
        return $this->belongsTo(config('form-bolt.models.Form'));
    }
}
