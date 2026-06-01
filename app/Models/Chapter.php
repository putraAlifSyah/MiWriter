<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'title',
        'content_html',
        'content_delta',
        'word_count',
        'order_number',
    ];

    protected function casts(): array
    {
        return [
            'content_delta' => 'array',
            'word_count' => 'integer',
            'order_number' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function snapshots()
    {
        return $this->hasMany(ChapterSnapshot::class)->orderByDesc('created_at');
    }
}
