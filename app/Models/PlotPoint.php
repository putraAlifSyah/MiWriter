<?php

namespace App\Models;

use App\Enums\PlotAct;
use App\Enums\PlotStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlotPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'title',
        'description',
        'act',
        'status',
        'color_label',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'act' => PlotAct::class,
            'status' => PlotStatus::class,
            'position' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'plot_point_characters');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'plot_point_locations');
    }
}
