<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Character;
use App\Models\DailyWordCount;
use App\Models\Location;
use App\Models\PlotPoint;
use App\Services\StatisticsService;
use App\Services\WritingTargetService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService,
        private WritingTargetService $targetService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $books = $user->books()->orderByDesc('updated_at')->get();
        $bookIds = $books->pluck('id');

        $timezone = $user->timezone ?? 'UTC';
        $today = Carbon::today($timezone)->toDateString();

        // Quick stats
        $totalBooks = $books->count();
        $totalChapters = Chapter::whereIn('book_id', $bookIds)->count();
        $totalWords = Chapter::whereIn('book_id', $bookIds)->sum('word_count');
        $totalCharacters = Character::whereIn('book_id', $bookIds)->count();
        $totalLocations = Location::whereIn('book_id', $bookIds)->count();

        // Words today
        $wordsToday = DailyWordCount::where('user_id', $user->id)
            ->where('date', $today)
            ->sum('word_count');

        // Streak
        $currentStreak = $this->statisticsService->getCurrentStreak($user);
        $longestStreak = $this->statisticsService->getLongestStreak($user);
        $averageDaily = $this->statisticsService->getAverageDailyWords($user);

        // Writing targets (from first book that has one, or null)
        $dailyProgress = null;
        $weeklyProgress = null;
        foreach ($books as $book) {
            $dp = $this->targetService->getDailyProgress($book, $user);
            if ($dp['has_target'] ?? false) {
                $dailyProgress = $dp;
                break;
            }
        }
        foreach ($books as $book) {
            $wp = $this->targetService->getWeeklyProgress($book, $user);
            if ($wp['has_target'] ?? false) {
                $weeklyProgress = $wp;
                break;
            }
        }

        // Words this week
        $startOfWeek = Carbon::now($timezone)->startOfWeek(Carbon::MONDAY)->toDateString();
        $endOfWeek = Carbon::now($timezone)->endOfWeek(Carbon::SUNDAY)->toDateString();
        $wordsThisWeek = DailyWordCount::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->sum('word_count');

        // Last 7 days word count for mini chart
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today($timezone)->subDays($i);
            $count = DailyWordCount::where('user_id', $user->id)
                ->where('date', $date->toDateString())
                ->sum('word_count');
            $last7Days[] = [
                'day' => $date->format('D'),
                'count' => $count,
            ];
        }

        // Recent activity - last 5 edited items across all books
        $recentChapters = Chapter::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->title,
                'type' => 'Chapter',
                'book_id' => $item->book_id,
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
                'url' => route('chapters.show', [$item->book_id, $item->id]),
            ]);

        $recentCharacters = Character::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'type' => 'Character',
                'book_id' => $item->book_id,
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
                'url' => route('characters.index', $item->book_id),
            ]);

        $recentLocations = Location::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'type' => 'Location',
                'book_id' => $item->book_id,
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
                'url' => route('locations.index', $item->book_id),
            ]);

        $recentPlotPoints = PlotPoint::whereIn('book_id', $bookIds)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->title,
                'type' => 'Plot Point',
                'book_id' => $item->book_id,
                'book_title' => $item->book->title,
                'updated_at' => $item->updated_at,
                'url' => route('plot.index', $item->book_id),
            ]);

        $recentActivity = collect()
            ->merge($recentChapters)
            ->merge($recentCharacters)
            ->merge($recentLocations)
            ->merge($recentPlotPoints)
            ->sortByDesc('updated_at')
            ->take(8)
            ->values();

        return view('dashboard.index', compact(
            'books', 'totalBooks', 'totalChapters', 'totalWords', 'totalCharacters', 'totalLocations',
            'wordsToday', 'wordsThisWeek', 'currentStreak', 'longestStreak', 'averageDaily',
            'dailyProgress', 'weeklyProgress', 'last7Days', 'recentActivity'
        ));
    }
}
