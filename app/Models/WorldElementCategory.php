<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldElementCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'book_id',
        'name',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
