<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\PlotPoint;
use App\Services\PlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlotController extends Controller
{
    public function __construct(private PlotService $plotService)
    {
    }

    public function index(Book $book): View
    {
        $plotPoints = $book->plotPoints()->with(['characters', 'locations'])->orderBy('position')->get();
        $characters = $book->characters()->get();
        $locations = $book->locations()->get();

        return view('plot.index', compact('book', 'plotPoints', 'characters', 'locations'));
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'act' => 'required|in:beginning,middle,end',
            'status' => 'required|in:planned,in_progress,completed',
            'color_label' => 'nullable|string|max:20',
            'character_ids' => 'nullable|array|max:20',
            'character_ids.*' => 'exists:characters,id',
            'location_ids' => 'nullable|array|max:10',
            'location_ids.*' => 'exists:locations,id',
        ]);

        $plotPoint = $this->plotService->create($book, $validated);

        if (!empty($validated['character_ids'])) {
            $this->plotService->linkCharacters($plotPoint, $validated['character_ids']);
        }

        if (!empty($validated['location_ids'])) {
            $this->plotService->linkLocations($plotPoint, $validated['location_ids']);
        }

        return response()->json([
            'message' => 'Plot point created.',
            'plot_point' => $plotPoint->load(['characters', 'locations']),
        ], 201);
    }

    public function update(Request $request, Book $book, PlotPoint $plotPoint): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'act' => 'required|in:beginning,middle,end',
            'status' => 'required|in:planned,in_progress,completed',
            'color_label' => 'nullable|string|max:20',
            'character_ids' => 'nullable|array|max:20',
            'character_ids.*' => 'exists:characters,id',
            'location_ids' => 'nullable|array|max:10',
            'location_ids.*' => 'exists:locations,id',
        ]);

        $plotPoint = $this->plotService->update($plotPoint, $validated);

        if (isset($validated['character_ids'])) {
            $this->plotService->linkCharacters($plotPoint, $validated['character_ids']);
        }

        if (isset($validated['location_ids'])) {
            $this->plotService->linkLocations($plotPoint, $validated['location_ids']);
        }

        return response()->json([
            'message' => 'Plot point updated.',
            'plot_point' => $plotPoint->load(['characters', 'locations']),
        ]);
    }

    public function destroy(Book $book, PlotPoint $plotPoint): JsonResponse
    {
        $this->plotService->delete($plotPoint);

        return response()->json(['message' => 'Plot point deleted.']);
    }

    public function reorder(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:plot_points,id',
        ]);

        $this->plotService->reorder($book, $request->order);

        return response()->json(['message' => 'Plot points reordered.']);
    }

    public function move(Request $request, Book $book, PlotPoint $plotPoint): JsonResponse
    {
        $validated = $request->validate([
            'act' => 'required|in:beginning,middle,end',
            'status' => 'required|in:planned,in_progress,completed',
        ]);

        $plotPoint->update($validated);

        return response()->json(['message' => 'Plot point moved.']);
    }
}
