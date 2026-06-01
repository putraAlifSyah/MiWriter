<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterRelationship extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'character_one_id',
        'character_two_id',
        'relationship_type',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function characterOne(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_one_id');
    }

    public function characterTwo(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_two_id');
    }
}
