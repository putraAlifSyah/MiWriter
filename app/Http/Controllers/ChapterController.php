<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Services\ChapterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function __construct(private ChapterService $chapterService)
    {
    }

    public function store(Book $book): JsonResponse
    {
        $chapter = $this->chapterService->create($book);

        return response()->json([
            'message' => 'Chapter created.',
            'chapter' => $chapter,
            'redirect' => route('chapters.show', [$book, $chapter]),
        ], 201);
    }

    public function show(Book $book, Chapter $chapter): View
    {
        $plotPoints = $book->plotPoints()->orderBy('position')->get();
        $allCharacters = $book->characters()->get();
        
        $text = strip_tags($chapter->content_html ?? '');
        $charactersInChapter = collect();
        $otherCharacters = collect();

        $colors = [
            ['bg' => 'rgba(239, 68, 68, 0.15)', 'border' => 'rgba(239, 68, 68, 0.6)', 'hex' => '#ef4444'],
            ['bg' => 'rgba(249, 115, 22, 0.15)', 'border' => 'rgba(249, 115, 22, 0.6)', 'hex' => '#f97316'],
            ['bg' => 'rgba(234, 179, 8, 0.15)', 'border' => 'rgba(234, 179, 8, 0.6)', 'hex' => '#eab308'],
            ['bg' => 'rgba(34, 197, 94, 0.15)', 'border' => 'rgba(34, 197, 94, 0.6)', 'hex' => '#22c55e'],
            ['bg' => 'rgba(20, 184, 166, 0.15)', 'border' => 'rgba(20, 184, 166, 0.6)', 'hex' => '#14b8a6'],
            ['bg' => 'rgba(59, 130, 246, 0.15)', 'border' => 'rgba(59, 130, 246, 0.6)', 'hex' => '#3b82f6'],
            ['bg' => 'rgba(99, 102, 241, 0.15)', 'border' => 'rgba(99, 102, 241, 0.6)', 'hex' => '#6366f1'],
            ['bg' => 'rgba(168, 85, 247, 0.15)', 'border' => 'rgba(168, 85, 247, 0.6)', 'hex' => '#a855f7'],
            ['bg' => 'rgba(236, 72, 153, 0.15)', 'border' => 'rgba(236, 72, 153, 0.6)', 'hex' => '#ec4899']
        ];
        
        $colorIndex = 0;
        foreach ($allCharacters as $char) {
            $char->color = (object)$colors[$colorIndex % count($colors)];
            $colorIndex++;

            $searchTerms = [$char->name];
            if ($char->aliases) {
                $searchTerms = array_merge($searchTerms, array_filter(array_map('trim', explode(',', $char->aliases))));
            }

            $mentionCount = 0;
            foreach ($searchTerms as $term) {
                if (empty($term)) continue;
                $pattern = '/\b' . preg_quote($term, '/') . '\b/iu';
                $mentionCount += preg_match_all($pattern, $text);
            }

            if ($mentionCount > 0) {
                $char->mention_count = $mentionCount;
                $charactersInChapter->push($char);
            } else {
                $otherCharacters->push($char);
            }
        }
        
        $charactersInChapter = $charactersInChapter->sortByDesc('mention_count')->values();
        
        $user = auth()->user();
        $userHasAi = !empty($user->ai_api_key) && !empty($user->ai_provider);
        
        return view('chapters.editor', compact('book', 'chapter', 'plotPoints', 'charactersInChapter', 'otherCharacters', 'userHasAi'));
    }

    public function update(Request $request, Book $book, Chapter $chapter): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|min:1|max:200',
        ]);

        $chapter->update(['title' => $request->title]);

        return response()->json([
            'message' => 'Chapter renamed.',
            'chapter' => $chapter,
        ]);
    }

    public function destroy(Book $book, Chapter $chapter): JsonResponse
    {
        $this->chapterService->delete($chapter);

        return response()->json([
            'message' => 'Chapter deleted.',
        ]);
    }

    public function reorder(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:chapters,id',
        ]);

        $this->chapterService->reorder($book, $request->order);

        return response()->json([
            'message' => 'Chapters reordered.',
        ]);
    }

    public function saveContent(Request $request, Book $book, Chapter $chapter): JsonResponse
    {
        $request->validate([
            'content_delta' => 'required|array',
            'content_html' => 'required|string',
        ]);

        $chapter = $this->chapterService->updateContent(
            $chapter,
            $request->content_delta,
            $request->content_html
        );

        return response()->json([
            'message' => 'Content saved.',
            'word_count' => $chapter->word_count,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function getSnapshots(Book $book, Chapter $chapter): JsonResponse
    {
        $snapshots = $chapter->snapshots()->select('id', 'created_at')->get();
        return response()->json(['snapshots' => $snapshots]);
    }

    public function saveSnapshot(Book $book, Chapter $chapter): JsonResponse
    {
        $snapshot = $chapter->snapshots()->create([
            'content_html' => $chapter->content_html,
            'content_delta' => $chapter->content_delta,
        ]);

        return response()->json([
            'message' => 'Snapshot saved.',
            'snapshot' => ['id' => $snapshot->id, 'created_at' => $snapshot->created_at]
        ], 201);
    }

    public function restoreSnapshot(Book $book, Chapter $chapter, \App\Models\ChapterSnapshot $snapshot): JsonResponse
    {
        if ($snapshot->chapter_id !== $chapter->id) {
            return response()->json(['error' => 'Invalid snapshot.'], 400);
        }

        $chapter->update([
            'content_html' => $snapshot->content_html,
            'content_delta' => $snapshot->content_delta,
        ]);

        return response()->json([
            'message' => 'Snapshot restored.',
            'chapter' => $chapter
        ]);
    }
}
