<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    public function overview(Book $book)
    {
        // Get most active characters (by number of chapters they appear in)
        $activeCharacters = $book->characters()
            ->get()
            ->sortByDesc(function ($char) {
                return substr_count($char->notes ?? '', ':**');
            })
            ->take(12);
            
        return view('wiki.overview', compact('book', 'activeCharacters'));
    }

    public function chapters(Book $book)
    {
        $chapters = $book->chapters()->whereNotNull('summary')->orderBy('order_number')->get();
        return view('wiki.chapters', compact('book', 'chapters'));
    }

    public function characters(Book $book, Request $request)
    {
        $query = $book->characters();
        
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        
        $characters = $query->orderBy('name')->get();
        return view('wiki.characters', compact('book', 'characters'));
    }

    public function character(Book $book, \App\Models\Character $character)
    {
        abort_if($character->book_id !== $book->id, 404);
        
        // Count total chapters
        $chapterCount = substr_count($character->notes ?? '', ':**');
        
        return view('wiki.character_show', compact('book', 'character', 'chapterCount'));
    }

    public function locations(Book $book, Request $request)
    {
        $query = $book->locations();
        
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        
        $locations = $query->orderBy('name')->get();
        return view('wiki.locations', compact('book', 'locations'));
    }

    public function worldElements(Book $book, Request $request)
    {
        $query = $book->worldElements();
        
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        $worldElements = $query->orderBy('name')->get();
        return view('wiki.world_elements', compact('book', 'worldElements'));
    }

    public function clear(Book $book)
    {
        // Check authorization if needed, assuming middleware handles it
        $book->characters()->delete();
        $book->locations()->delete();
        $book->worldElements()->delete();
        $book->chapters()->update([
            'summary' => null,
            'extracted_entities' => null,
        ]);

        return redirect()->route('books.wiki.index', $book)
                         ->with('success', 'Wiki has been cleared successfully.');
    }
}
