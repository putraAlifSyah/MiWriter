<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'timezone',
        'date_format',
        'ai_provider',
        'ai_model',
        'ai_api_key',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'ai_api_key',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function writingSessions(): HasMany
    {
        return $this->hasMany(WritingSession::class);
    }

    public function dailyWordCounts(): HasMany
    {
        return $this->hasMany(DailyWordCount::class);
    }

    public function writingTargets(): HasMany
    {
        return $this->hasMany(WritingTarget::class);
    }
}
