<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\StatisticsService;
use App\Services\WritingTargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService,
        private WritingTargetService $targetService,
    ) {
    }

    public function show(Request $request, Book $book): View
    {
        $user = $request->user();

        $stats = [
            'total_word_count' => $this->statisticsService->getTotalWordCount($book),
            'current_streak' => $this->statisticsService->getCurrentStreak($user),
            'longest_streak' => $this->statisticsService->getLongestStreak($user),
            'average_daily' => $this->statisticsService->getAverageDailyWords($user),
            'estimated_completion' => $this->statisticsService->getEstimatedCompletion($book, $user),
            'daily_progress' => $this->targetService->getDailyProgress($book, $user),
            'weekly_progress' => $this->targetService->getWeeklyProgress($book, $user),
        ];

        // Advanced Goal Tracker (NaNoWriMo Mode)
        $projectGoal = null;
        if ($book->target_word_count && $book->target_deadline) {
            $totalWords = $stats['total_word_count'];
            $target = $book->target_word_count;
            $deadline = \Carbon\Carbon::parse($book->target_deadline);
            $today = \Carbon\Carbon::today();
            
            $daysRemaining = max(1, $today->diffInDays($deadline, false));
            $wordsRemaining = max(0, $target - $totalWords);
            $requiredPerDay = ceil($wordsRemaining / $daysRemaining);
            $percentage = min(100, $target > 0 ? round(($totalWords / $target) * 100) : 0);

            $projectGoal = [
                'target' => $target,
                'deadline' => $deadline,
                'days_remaining' => $daysRemaining,
                'words_remaining' => $wordsRemaining,
                'required_per_day' => $requiredPerDay,
                'percentage' => $percentage,
                'is_overdue' => $today->gt($deadline) && $wordsRemaining > 0,
            ];
        }

        return view('statistics.index', compact('book', 'stats', 'projectGoal'));
    }

    public function heatmap(Request $request): JsonResponse
    {
        $data = $this->statisticsService->getHeatmapData($request->user());
        return response()->json($data);
    }

    public function progressChart(Book $book): JsonResponse
    {
        $data = $this->statisticsService->getProgressChartData($book);
        return response()->json($data);
    }
}
