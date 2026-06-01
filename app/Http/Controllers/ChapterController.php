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

        foreach ($allCharacters as $char) {
            $searchTerms = [$char->name];
            if ($char->aliases) {
                $searchTerms = array_merge($searchTerms, array_filter(array_map('trim', explode(',', $char->aliases))));
            }

            $isPresent = false;
            foreach ($searchTerms as $term) {
                if (empty($term)) continue;
                $pattern = '/\b' . preg_quote($term, '/') . '\b/iu';
                if (preg_match($pattern, $text)) {
                    $isPresent = true;
                    break;
                }
            }

            if ($isPresent) {
                $charactersInChapter->push($char);
            } else {
                $otherCharacters->push($char);
            }
        }
        
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
