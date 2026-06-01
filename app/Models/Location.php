<?php

namespace App\Models;

use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'parent_id',
        'name',
        'type',
        'description',
        'atmosphere',
        'notable_features',
        'notes',
        'image_path',
        'depth',
    ];

    protected function casts(): array
    {
        return [
            'type' => LocationType::class,
            'depth' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function plotPoints(): BelongsToMany
    {
        return $this->belongsToMany(PlotPoint::class, 'plot_point_locations');
    }
}
