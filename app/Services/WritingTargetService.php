<?php

namespace App\Services;

use App\Models\Book;
use App\Models\DailyWordCount;
use App\Models\User;
use App\Models\WritingTarget;
use Illuminate\Support\Carbon;

class WritingTargetService
{
    public function setTarget(Book $book, User $user, string $type, int $wordCount): WritingTarget
    {
        return WritingTarget::updateOrCreate(
            ['book_id' => $book->id, 'user_id' => $user->id, 'type' => $type],
            ['word_count' => $wordCount]
        );
    }

    public function getDailyProgress(Book $book, User $user): array
    {
        $target = WritingTarget::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->where('type', 'daily')
            ->first();

        if (!$target) {
            return ['has_target' => false];
        }

        $today = Carbon::today($user->timezone ?? 'UTC')->toDateString();

        $wordsToday = DailyWordCount::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('date', $today)
            ->sum('word_count');

        $percentage = min(100, (int) floor(($wordsToday / $target->word_count) * 100));

        return [
            'has_target' => true,
            'target' => $target->word_count,
            'current' => $wordsToday,
            'percentage' => $percentage,
            'met' => $wordsToday >= $target->word_count,
        ];
    }

    public function getWeeklyProgress(Book $book, User $user): array
    {
        $target = WritingTarget::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->where('type', 'weekly')
            ->first();

        if (!$target) {
            return ['has_target' => false];
        }

        $startOfWeek = Carbon::now($user->timezone ?? 'UTC')->startOfWeek(Carbon::MONDAY)->toDateString();
        $endOfWeek = Carbon::now($user->timezone ?? 'UTC')->endOfWeek(Carbon::SUNDAY)->toDateString();

        $wordsThisWeek = DailyWordCount::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->sum('word_count');

        $percentage = min(100, (int) floor(($wordsThisWeek / $target->word_count) * 100));

        return [
            'has_target' => true,
            'target' => $target->word_count,
            'current' => $wordsThisWeek,
            'percentage' => $percentage,
            'met' => $wordsThisWeek >= $target->word_count,
        ];
    }

    public function checkTargetMet(Book $book, User $user, string $type): bool
    {
        $progress = $type === 'daily'
            ? $this->getDailyProgress($book, $user)
            : $this->getWeeklyProgress($book, $user);

        return $progress['has_target'] && $progress['met'];
    }
}
