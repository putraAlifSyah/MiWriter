<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WritingTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'type',
        'word_count',
    ];

    protected function casts(): array
    {
        return [
            'word_count' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
