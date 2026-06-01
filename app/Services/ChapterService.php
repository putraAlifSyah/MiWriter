<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChapterService
{
    public function create(Book $book): Chapter
    {
        $nextOrder = $book->chapters()->max('order_number') + 1;

        return $book->chapters()->create([
            'title' => "Chapter {$nextOrder}",
            'order_number' => $nextOrder,
            'word_count' => 0,
        ]);
    }

    public function updateContent(Chapter $chapter, array $delta, string $html): Chapter
    {
        $wordCount = $this->calculateWordCount($html);

        $chapter->update([
            'content_html' => $html,
            'content_delta' => $delta,
            'word_count' => $wordCount,
        ]);

        // Touch the book's updated_at
        $chapter->book->touch();

        return $chapter->fresh();
    }

    public function reorder(Book $book, array $orderedIds): void
    {
        DB::transaction(function () use ($book, $orderedIds) {
            $bookChapterIds = $book->chapters()->pluck('id')->toArray();
            $diff = array_diff($orderedIds, $bookChapterIds);

            if (!empty($diff) || count($orderedIds) !== count($bookChapterIds)) {
                throw new InvalidArgumentException('Invalid chapter IDs for reorder');
            }

            foreach ($orderedIds as $index => $chapterId) {
                Chapter::where('id', $chapterId)->update(['order_number' => $index + 1]);
            }
        });
    }

    public function delete(Chapter $chapter): void
    {
        DB::transaction(function () use ($chapter) {
            $book = $chapter->book;
            $deletedOrder = $chapter->order_number;

            $chapter->delete();

            $book->chapters()
                ->where('order_number', '>', $deletedOrder)
                ->decrement('order_number');
        });
    }

    public function calculateWordCount(string $htmlContent): int
    {
        $plainText = strip_tags($htmlContent);
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');
        $plainText = trim(preg_replace('/\s+/', ' ', $plainText));

        if ($plainText === '') {
            return 0;
        }

        return count(preg_split('/\s+/', $plainText));
    }
}
