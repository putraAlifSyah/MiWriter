<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocationService
{
    public function create(Book $book, array $data): Location
    {
        if (isset($data['parent_id']) && $data['parent_id']) {
            $parent = Location::findOrFail($data['parent_id']);

            if ($parent->depth >= 4) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Maximum hierarchy depth of 5 levels exceeded.',
                ]);
            }

            $data['depth'] = $parent->depth + 1;
        } else {
            $data['depth'] = 0;
        }

        return $book->locations()->create($data);
    }

    public function update(Location $location, array $data): Location
    {
        $location->update($data);
        return $location->fresh();
    }

    public function delete(Location $location): void
    {
        DB::transaction(function () use ($location) {
            $parentId = $location->parent_id;

            // Reassign children to deleted location's parent
            $children = $location->children()->get();
            foreach ($children as $child) {
                $newDepth = $parentId ? Location::find($parentId)->depth + 1 : 0;
                $child->update([
                    'parent_id' => $parentId,
                    'depth' => $newDepth,
                ]);
                $this->recalculateChildDepths($child);
            }

            $location->delete();
        });
    }

    private function recalculateChildDepths(Location $location): void
    {
        $children = $location->children()->get();
        foreach ($children as $child) {
            $child->update(['depth' => $location->depth + 1]);
            $this->recalculateChildDepths($child);
        }
    }

    public function getHierarchyTree(Book $book): array
    {
        $locations = $book->locations()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return $this->buildTree($locations);
    }

    private function buildTree(Collection $locations): array
    {
        return $locations->map(function (Location $location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'type' => $location->type->value,
                'depth' => $location->depth,
                'children' => $location->children->isNotEmpty()
                    ? $this->buildTree($location->children)
                    : [],
            ];
        })->toArray();
    }
}
