<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = [
        'email',
        'attempts',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'locked_until' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
