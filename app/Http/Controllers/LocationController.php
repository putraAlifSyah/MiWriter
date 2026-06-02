<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct(private LocationService $locationService)
    {
    }

    public function index(Request $request, Book $book): View
    {
        $query = $book->locations();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $locations = $query->orderBy('name')->get();

        return view('locations.index', compact('book', 'locations'));
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:1|max:200',
            'type' => 'required|in:city,building,landscape,realm,other',
            'parent_id' => 'nullable|exists:locations,id',
            'description' => 'nullable|string',
            'atmosphere' => 'nullable|string',
            'notable_features' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $location = $this->locationService->create($book, $validated);

        return response()->json([
            'message' => 'Location created.',
            'location' => $location,
        ], 201);
    }

    public function show(Book $book, Location $location)
    {
        abort_if($location->book_id !== $book->id, 404);
        return view('locations.show', compact('book', 'location'));
    }

    public function update(Request $request, Book $book, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:1|max:200',
            'type' => 'required|in:city,building,landscape,realm,other',
            'description' => 'nullable|string',
            'atmosphere' => 'nullable|string',
            'notable_features' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $location = $this->locationService->update($location, $validated);

        return response()->json([
            'message' => 'Location updated.',
            'location' => $location,
        ]);
    }

    public function destroy(Book $book, Location $location): JsonResponse
    {
        $this->locationService->delete($location);

        return response()->json(['message' => 'Location deleted.']);
    }

    public function uploadImage(Request $request, Book $book, Location $location): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('image')->store("locations/{$location->id}", 'public');
        $location->update(['image_path' => $path]);

        return response()->json([
            'message' => 'Image uploaded.',
            'path' => $path,
        ]);
    }

    public function hierarchy(Book $book): JsonResponse
    {
        $tree = $this->locationService->getHierarchyTree($book);
        return response()->json($tree);
    }
}
