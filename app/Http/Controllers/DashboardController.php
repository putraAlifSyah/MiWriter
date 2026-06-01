<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Character;
use App\Models\DailyWordCount;
use App\Models\Location;
use App\Models\PlotPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $books = $user->books()->orderByDesc('updated_at')->get();

        $today = Carbon::today($user->timezone ?? 'UTC')->toDateString();
        $wordsToday = DailyWordCount::where('user_id', $user->id)
            ->where('date', $today)
            ->sum('word_count');

        // Recent activity - last 5 edited items across all books
        $bookIds = $books->pluck('id');

        $recentChapters = Chapter::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->title,
                'type' => 'chapter',
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
            ]);

        $recentCharacters = Character::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'type' => 'character',
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
            ]);

        $recentLocations = Location::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'type' => 'location',
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
            ]);

        $recentPlotPoints = PlotPoint::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->title,
                'type' => 'plot point',
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
            ]);

        $recentActivity = collect()
            ->merge($recentChapters)
            ->merge($recentCharacters)
            ->merge($recentLocations)
            ->merge($recentPlotPoints)
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();

        return view('dashboard.index', compact('books', 'wordsToday', 'recentActivity'));
    }
}
