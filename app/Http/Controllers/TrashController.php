<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Character;

class TrashController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        $books = Book::onlyTrashed()->where('user_id', $userId)->get();
        
        $chapters = Chapter::onlyTrashed()
            ->whereHas('book', function ($q) use ($userId) {
                $q->withTrashed()->where('user_id', $userId);
            })
            ->with(['book' => function($q) { $q->withTrashed(); }])
            ->get();
            
        $characters = Character::onlyTrashed()
            ->whereHas('book', function ($q) use ($userId) {
                $q->withTrashed()->where('user_id', $userId);
            })
            ->with(['book' => function($q) { $q->withTrashed(); }])
            ->get();

        return view('trash.index', compact('books', 'chapters', 'characters'));
    }

    public function restore($type, $id)
    {
        $userId = Auth::id();
        $item = $this->findItem($type, $id, $userId);
        
        if (!$item) return redirect()->back()->with('error', 'Item not found.');
        
        $item->restore();
        return redirect()->back()->with('success', ucfirst($type) . ' restored successfully.');
    }

    public function forceDelete($type, $id)
    {
        $userId = Auth::id();
        $item = $this->findItem($type, $id, $userId);
        
        if (!$item) return redirect()->back()->with('error', 'Item not found.');
        
        $item->forceDelete();
        return redirect()->back()->with('success', ucfirst($type) . ' permanently deleted.');
    }

    private function findItem($type, $id, $userId)
    {
        if ($type === 'book') {
            return Book::onlyTrashed()->where('user_id', $userId)->where('id', $id)->first();
        }
        
        if ($type === 'chapter') {
            return Chapter::onlyTrashed()->whereHas('book', function ($q) use ($userId) {
                $q->withTrashed()->where('user_id', $userId);
            })->where('id', $id)->first();
        }
        
        if ($type === 'character') {
            return Character::onlyTrashed()->whereHas('book', function ($q) use ($userId) {
                $q->withTrashed()->where('user_id', $userId);
            })->where('id', $id)->first();
        }
        
        return null;
    }
}
