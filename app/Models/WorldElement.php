<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorldElement extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'name',
        'category',
        'image_path',
        'description',
        'rules_laws',
        'notes',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function references(): BelongsToMany
    {
        return $this->belongsToMany(WorldElement::class, 'world_element_references', 'source_id', 'target_id');
    }

    public function referencedBy(): BelongsToMany
    {
        return $this->belongsToMany(WorldElement::class, 'world_element_references', 'target_id', 'source_id');
    }
}
