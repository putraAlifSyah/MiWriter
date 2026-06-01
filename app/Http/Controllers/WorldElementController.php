<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\WorldElement;
use App\Services\WorldBuildingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorldElementController extends Controller
{
    public function __construct(private WorldBuildingService $worldService)
    {
    }

    public function index(Book $book): View
    {
        $grouped = $this->worldService->getGroupedByCategory($book);
        $categories = $this->worldService->getCategories($book);

        return view('world.index', compact('book', 'grouped', 'categories'));
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string|max:10000',
            'rules_laws' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
        ]);

        $element = $this->worldService->create($book, $validated);

        return response()->json([
            'message' => 'World element created.',
            'element' => $element,
        ], 201);
    }

    public function update(Request $request, Book $book, WorldElement $element): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string|max:10000',
            'rules_laws' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
        ]);

        $element = $this->worldService->update($element, $validated);

        return response()->json([
            'message' => 'World element updated.',
            'element' => $element,
        ]);
    }

    public function destroy(Book $book, WorldElement $element): JsonResponse
    {
        $this->worldService->delete($element);

        return response()->json(['message' => 'World element deleted.']);
    }
}
