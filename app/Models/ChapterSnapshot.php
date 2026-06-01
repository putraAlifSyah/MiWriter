<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterSnapshot extends Model
{
    protected $fillable = [
        'chapter_id',
        'content_html',
        'content_delta',
    ];

    protected $casts = [
        'content_delta' => 'array',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
