<?php

namespace App\Models;

use App\Enums\CharacterRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Character extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'name',
        'aliases',
        'role',
        'physical_description',
        'personality_traits',
        'backstory',
        'motivations',
        'notes',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'role' => CharacterRole::class,
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(CharacterRelationship::class, 'character_one_id');
    }

    public function relatedCharacters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_relationships', 'character_one_id', 'character_two_id')
            ->withPivot('relationship_type');
    }

    public function plotPoints(): BelongsToMany
    {
        return $this->belongsToMany(PlotPoint::class, 'plot_point_characters');
    }
}
