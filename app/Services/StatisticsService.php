<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\DailyWordCount;
use App\Models\User;
use App\Models\WritingSession;
use App\Models\WritingTarget;
use Illuminate\Support\Carbon;

class StatisticsService
{
    public function getTotalWordCount(Book $book): int
    {
        return $book->chapters()->sum('word_count');
    }

    public function getCurrentStreak(User $user): int
    {
        $dailyTarget = WritingTarget::where('user_id', $user->id)
            ->where('type', 'daily')
            ->first();

        if (!$dailyTarget) {
            return 0;
        }

        $targetWordCount = $dailyTarget->word_count;
        $timezone = $user->timezone ?? 'UTC';
        $today = Carbon::today($timezone);
        $streak = 0;
        $checkDate = $today->copy();

        // Check today first
        $todayWords = DailyWordCount::where('user_id', $user->id)
            ->where('date', $checkDate->toDateString())
            ->sum('word_count');

        if ($todayWords >= $targetWordCount) {
            $streak++;
            $checkDate->subDay();
        } else {
            // Today hasn't met target, check from yesterday
            $checkDate->subDay();
        }

        while (true) {
            $dailyWords = DailyWordCount::where('user_id', $user->id)
                ->where('date', $checkDate->toDateString())
                ->sum('word_count');

            if ($dailyWords >= $targetWordCount) {
                $streak++;
                $checkDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    public function getLongestStreak(User $user): int
    {
        $dailyTarget = WritingTarget::where('user_id', $user->id)
            ->where('type', 'daily')
            ->first();

        if (!$dailyTarget) {
            return 0;
        }

        $targetWordCount = $dailyTarget->word_count;

        $dailyCounts = DailyWordCount::where('user_id', $user->id)
            ->selectRaw('date, SUM(word_count) as total_words')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $longest = 0;
        $current = 0;
        $previousDate = null;

        foreach ($dailyCounts as $record) {
            $currentDate = Carbon::parse($record->date);

            if ($record->total_words >= $targetWordCount) {
                if ($previousDate && $currentDate->diffInDays($previousDate) === 1) {
                    $current++;
                } else {
                    $current = 1;
                }
                $longest = max($longest, $current);
            } else {
                $current = 0;
            }

            $previousDate = $currentDate;
        }

        return $longest;
    }

    public function getAverageDailyWords(User $user, int $days = 30): int
    {
        $startDate = Carbon::today($user->timezone ?? 'UTC')->subDays($days - 1)->toDateString();

        $totalWords = DailyWordCount::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->sum('word_count');

        return (int) round($totalWords / $days);
    }

    public function getHeatmapData(User $user, int $months = 12): array
    {
        $timezone = $user->timezone ?? 'UTC';
        $startDate = Carbon::today($timezone)->subMonths($months);
        $endDate = Carbon::today($timezone);

        $dailyTarget = WritingTarget::where('user_id', $user->id)
            ->where('type', 'daily')
            ->first();

        $targetWordCount = $dailyTarget?->word_count;

        $dailyCounts = DailyWordCount::where('user_id', $user->id)
            ->where('date', '>=', $startDate->toDateString())
            ->selectRaw('date, SUM(word_count) as total_words')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $heatmap = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->toDateString();
            $wordCount = isset($dailyCounts[$dateStr]) ? (int) $dailyCounts[$dateStr]->total_words : 0;

            $heatmap[] = [
                'date' => $dateStr,
                'count' => $wordCount,
                'intensity' => $this->calculateIntensity($wordCount, $targetWordCount),
            ];

            $currentDate->addDay();
        }

        return $heatmap;
    }

    private function calculateIntensity(int $wordCount, ?int $target): int
    {
        if ($wordCount === 0) return 0;
        if ($target === null || $target === 0) return 1;

        $percentage = ($wordCount / $target) * 100;

        if ($percentage >= 100) return 4;
        if ($percentage > 50) return 3;
        if ($percentage > 25) return 2;
        return 1;
    }

    public function getProgressChartData(Book $book, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        $data = [];
        $cumulative = 0;

        // Get base word count before the period
        $baseWordCount = DailyWordCount::where('book_id', $book->id)
            ->where('date', '<', $startDate->toDateString())
            ->sum('word_count');

        $dailyCounts = DailyWordCount::where('book_id', $book->id)
            ->where('date', '>=', $startDate->toDateString())
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($item) => $item->date->toDateString());

        $cumulative = $baseWordCount;
        $currentDate = $startDate->copy();

        for ($i = 0; $i < $days; $i++) {
            $dateStr = $currentDate->toDateString();
            $dayWords = isset($dailyCounts[$dateStr]) ? $dailyCounts[$dateStr]->word_count : 0;
            $cumulative += $dayWords;

            $data[] = [
                'date' => $dateStr,
                'words' => $cumulative,
            ];

            $currentDate->addDay();
        }

        return $data;
    }

    public function recordSession(User $user, Chapter $chapter, int $wordsWritten, int $durationSeconds): WritingSession
    {
        $today = Carbon::today($user->timezone ?? 'UTC')->toDateString();

        $session = WritingSession::create([
            'user_id' => $user->id,
            'chapter_id' => $chapter->id,
            'book_id' => $chapter->book_id,
            'words_written' => $wordsWritten,
            'duration_seconds' => $durationSeconds,
            'session_date' => $today,
        ]);

        // Update daily word count aggregate
        if ($wordsWritten > 0) {
            DailyWordCount::updateOrCreate(
                ['user_id' => $user->id, 'book_id' => $chapter->book_id, 'date' => $today],
                ['word_count' => \DB::raw("word_count + {$wordsWritten}")]
            );
        }

        return $session;
    }

    public function getEstimatedCompletion(Book $book, User $user): ?Carbon
    {
        $targetWordCount = $book->target_word_count;
        if (!$targetWordCount) {
            return null;
        }

        $currentWordCount = $this->getTotalWordCount($book);
        if ($currentWordCount >= $targetWordCount) {
            return null;
        }

        $avgDaily = $this->getAverageDailyWords($user);
        if ($avgDaily <= 0) {
            return null;
        }

        $remainingWords = $targetWordCount - $currentWordCount;
        $daysNeeded = (int) ceil($remainingWords / $avgDaily);

        return Carbon::today($user->timezone ?? 'UTC')->addDays($daysNeeded);
    }
}
