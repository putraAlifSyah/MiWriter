<?php

namespace App\Services;

use App\Models\Book;
use App\Models\WorldElement;
use App\Models\WorldElementCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorldBuildingService
{
    public function create(Book $book, array $data): WorldElement
    {
        return $book->worldElements()->create($data);
    }

    public function update(WorldElement $element, array $data): WorldElement
    {
        $element->update($data);
        return $element->fresh();
    }

    public function delete(WorldElement $element): void
    {
        DB::transaction(function () use ($element) {
            // Remove all cross-references (both directions)
            DB::table('world_element_references')
                ->where('source_id', $element->id)
                ->orWhere('target_id', $element->id)
                ->delete();

            $element->delete();
        });
    }

    public function addCrossReference(WorldElement $source, WorldElement $target): void
    {
        if ($source->book_id !== $target->book_id) {
            throw new \InvalidArgumentException('Elements must belong to the same book.');
        }

        $source->references()->attach($target->id);
    }

    public function removeCrossReference(WorldElement $source, WorldElement $target): void
    {
        $source->references()->detach($target->id);
    }

    public function getGroupedByCategory(Book $book): Collection
    {
        return $book->worldElements()
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }

    public function createCategory(Book $book, string $name): WorldElementCategory
    {
        return WorldElementCategory::create([
            'book_id' => $book->id,
            'name' => $name,
        ]);
    }

    public function getCategories(Book $book): Collection
    {
        $predefined = collect(['magic system', 'culture', 'history', 'technology', 'religion', 'politics', 'economy']);
        $custom = WorldElementCategory::where('book_id', $book->id)->pluck('name');

        return $predefined->merge($custom)->unique();
    }
}
