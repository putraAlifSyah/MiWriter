<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $searchService)
    {
    }

    public function search(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $results = $this->searchService->search($book, $request->query('query'));

        return response()->json($results);
    }
}
