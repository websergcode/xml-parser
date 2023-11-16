<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'url',
        'created_at',
        'updated_at',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
