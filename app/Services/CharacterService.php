<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Character;
use App\Models\CharacterRelationship;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CharacterService
{
    public function create(Book $book, array $data): Character
    {
        return $book->characters()->create($data);
    }

    public function update(Character $character, array $data): Character
    {
        $character->update($data);
        return $character->fresh();
    }

    public function delete(Character $character): void
    {
        DB::transaction(function () use ($character) {
            // Delete all relationships referencing this character
            CharacterRelationship::where('character_one_id', $character->id)
                ->orWhere('character_two_id', $character->id)
                ->delete();

            $character->delete();
        });
    }

    public function addRelationship(Character $char1, Character $char2, string $type): CharacterRelationship
    {
        if ($char1->book_id !== $char2->book_id) {
            throw new InvalidArgumentException('Characters must belong to the same book.');
        }

        if ($char1->id === $char2->id) {
            throw new InvalidArgumentException('Cannot create a relationship between a character and itself.');
        }

        // Ensure consistent ordering
        $ids = [$char1->id, $char2->id];
        sort($ids);

        return CharacterRelationship::create([
            'character_one_id' => $ids[0],
            'character_two_id' => $ids[1],
            'relationship_type' => $type,
        ]);
    }

    public function removeRelationship(CharacterRelationship $relationship): void
    {
        $relationship->delete();
    }

    public function getRelationshipMap(Book $book): array
    {
        $characters = $book->characters()->get();
        $relationships = CharacterRelationship::whereIn('character_one_id', $characters->pluck('id'))
            ->orWhereIn('character_two_id', $characters->pluck('id'))
            ->get();

        return [
            'characters' => $characters->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'role' => $c->role->value,
                'image_url' => $c->image_path ? asset('storage/' . $c->image_path) : null,
            ])->toArray(),
            'relationships' => $relationships->map(fn ($r) => [
                'id' => $r->id,
                'character_one_id' => $r->character_one_id,
                'character_two_id' => $r->character_two_id,
                'type' => $r->relationship_type,
            ])->toArray(),
        ];
    }
}
