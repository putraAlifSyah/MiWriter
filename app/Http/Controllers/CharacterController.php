<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Character;
use App\Services\CharacterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function __construct(private CharacterService $characterService)
    {
    }

    public function index(Request $request, Book $book): View
    {
        $query = $book->characters();

        if ($search = $request->get('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $characters = $query->orderBy('name')->get();

        return view('characters.index', compact('book', 'characters'));
    }

    public function show(Book $book, Character $character): View
    {
        // Ensure character belongs to the book
        abort_if($character->book_id !== $book->id, 404);

        return view('characters.show', compact('book', 'character'));
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:protagonist,antagonist,supporting,minor',
            'physical_description' => 'nullable|string|max:2000',
            'personality_traits' => 'nullable|string|max:2000',
            'backstory' => 'nullable|string|max:5000',
            'motivations' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:5000',
        ]);

        $character = $this->characterService->create($book, $validated);

        return response()->json([
            'message' => 'Character created.',
            'character' => $character,
        ], 201);
    }

    public function update(Request $request, Book $book, Character $character): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:protagonist,antagonist,supporting,minor',
            'physical_description' => 'nullable|string|max:2000',
            'personality_traits' => 'nullable|string|max:2000',
            'backstory' => 'nullable|string|max:5000',
            'motivations' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:5000',
        ]);

        $character = $this->characterService->update($character, $validated);

        return response()->json([
            'message' => 'Character updated.',
            'character' => $character,
        ]);
    }

    public function destroy(Book $book, Character $character): JsonResponse
    {
        $this->characterService->delete($character);

        return response()->json(['message' => 'Character deleted.']);
    }

    public function uploadImage(Request $request, Book $book, Character $character): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('image')->store("characters/{$character->id}", 'public');
        $character->update(['image_path' => $path]);

        return response()->json([
            'message' => 'Image uploaded.',
            'path' => $path,
        ]);
    }

    public function relationships(Book $book): JsonResponse
    {
        $map = $this->characterService->getRelationshipMap($book);
        return response()->json($map);
    }

    public function generateAiRelationships(Request $request, Book $book, \App\Services\AiService $aiService): JsonResponse
    {
        try {
            $relationships = $aiService->generateCharacterRelationships($request->user(), $book);
            
            // Delete old relationships
            \App\Models\CharacterRelationship::whereIn('character_one_id', $book->characters()->pluck('id'))
                ->orWhereIn('character_two_id', $book->characters()->pluck('id'))
                ->delete();

            // Insert new ones
            foreach ($relationships as $rel) {
                // Ensure characters exist and belong to this book
                $c1 = $book->characters()->find($rel['character_one_id']);
                $c2 = $book->characters()->find($rel['character_two_id']);
                
                if ($c1 && $c2 && $c1->id !== $c2->id) {
                    \App\Models\CharacterRelationship::create([
                        'character_one_id' => $c1->id,
                        'character_two_id' => $c2->id,
                        'relationship_type' => $rel['type'],
                    ]);
                }
            }

            return response()->json(['message' => 'Relationships generated.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
