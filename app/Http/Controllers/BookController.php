<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function __construct(private BookService $bookService)
    {
    }

    public function index(Request $request): View
    {
        $books = $request->user()->books()->orderByDesc('updated_at')->get();
        return view('books.index', compact('books'));
    }

    public function create(): View
    {
        return view('books.create');
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = $this->bookService->create($request->user(), $request->validated());
        return redirect()->route('books.show', $book)->with('success', 'Book created successfully.');
    }

    public function show(Book $book): View
    {
        $book->load(['chapters', 'characters', 'locations', 'plotPoints', 'worldElements']);
        return view('books.show', compact('book'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->bookService->update($book, $request->validated());
        return redirect()->route('books.show', $book)->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->bookService->delete($book);
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }

    public function uploadCover(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'cover' => 'required|file|mimes:jpeg,png,webp|max:5120',
        ]);

        $file = $request->file('cover');

        // Validate dimensions
        $image = getimagesize($file->getPathname());
        if ($image[0] < 600 || $image[1] < 900) {
            return response()->json([
                'message' => 'Image must be at least 600x900 pixels.',
            ], 422);
        }

        $path = $this->bookService->uploadCover($book, $file);

        return response()->json([
            'message' => 'Cover uploaded successfully.',
            'path' => $path,
        ]);
    }

    public function removeCover(Book $book): JsonResponse
    {
        $this->bookService->removeCover($book);

        return response()->json([
            'message' => 'Cover removed successfully.',
        ]);
    }
}
