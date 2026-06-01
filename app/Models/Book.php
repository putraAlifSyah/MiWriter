<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'genre',
        'synopsis',
        'status',
        'cover_image_path',
        'cover_thumbnail_path',
        'target_word_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order_number');
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function plotPoints(): HasMany
    {
        return $this->hasMany(PlotPoint::class)->orderBy('position');
    }

    public function worldElements(): HasMany
    {
        return $this->hasMany(WorldElement::class);
    }

    public function writingTargets(): HasMany
    {
        return $this->hasMany(WritingTarget::class);
    }

    public function writingSessions(): HasMany
    {
        return $this->hasMany(WritingSession::class);
    }

    public function getTotalWordCountAttribute(): int
    {
        return $this->chapters()->sum('word_count');
    }
}
