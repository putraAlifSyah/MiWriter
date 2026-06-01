<?php

namespace App\Http\Controllers;

use App\Models\AiMessage;
use App\Models\Book;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private AiService $aiService)
    {
    }

    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|min:1|max:2000',
            'book_id' => 'nullable|integer|exists:books,id',
            'chapter_ids' => 'nullable|array',
            'chapter_ids.*' => 'integer|exists:chapters,id',
        ]);

        $user = $request->user();
        $book = null;
        $chapterIds = $request->input('chapter_ids', []);

        if ($request->book_id) {
            $book = Book::where('id', $request->book_id)
                ->where('user_id', $user->id)
                ->first();
        }

        try {
            $result = $this->aiService->ask($user, $request->message, $book, $chapterIds ?: null);

            // Save the conversation to history
            $this->aiService->saveMessage($user, 'user', $request->message, $book?->id, $chapterIds ?: null);

            $assistantMetadata = null;
            if (!empty($result['action']) && !empty($result['created'])) {
                $assistantMetadata = [
                    'action' => $result['action'],
                    'created' => $result['created'],
                ];
            }

            $this->aiService->saveMessage(
                $user,
                'assistant',
                $result['response'],
                $book?->id,
                $chapterIds ?: null,
                $assistantMetadata
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function inline(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'instruction' => 'required|string|max:200',
            'book_id' => 'nullable|integer|exists:books,id',
        ]);

        $user = $request->user();
        $book = null;

        if ($request->book_id) {
            $book = Book::where('id', $request->book_id)
                ->where('user_id', $user->id)
                ->first();
        }

        try {
            $result = $this->aiService->inlineEdit($user, $request->text, $request->instruction, $book);
            return response()->json(['result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function betaRead(Request $request, Book $book, \App\Models\Chapter $chapter): JsonResponse
    {
        $user = $request->user();

        if ($book->user_id !== $user->id || $chapter->book_id !== $book->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'text' => 'required|string',
        ]);

        try {
            $result = $this->aiService->betaRead($user, $book, $chapter, $request->text);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function extractCharacters(Request $request, Book $book, \App\Models\Chapter $chapter): JsonResponse
    {
        $user = $request->user();

        if ($book->user_id !== $user->id || $chapter->book_id !== $book->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $createdCount = $this->aiService->extractCharacters($user, $book, $chapter);
            return response()->json(['success' => true, 'created' => $createdCount]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function aiWizard(Request $request, Book $book): JsonResponse
    {
        $user = $request->user();

        if ($book->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'premise' => 'required|string',
            'framework' => 'required|string',
        ]);

        try {
            $plotPoints = $this->aiService->aiWizard($user, $book, $request->premise, $request->framework);
            
            // Insert into database
            $created = [];
            $position = $book->plotPoints()->max('position') + 1;
            
            foreach ($plotPoints as $point) {
                $created[] = $book->plotPoints()->create([
                    'title' => substr($point['title'] ?? 'Untitled', 0, 150),
                    'description' => $point['description'] ?? '',
                    'act' => in_array($point['act'] ?? '', ['beginning', 'middle', 'end']) ? $point['act'] : 'beginning',
                    'status' => 'planned',
                    'position' => $position++,
                ]);
            }

            return response()->json(['success' => true, 'created' => count($created)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get recent AI chat history (max 20 messages).
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $history = $this->aiService->getRecentHistory($user, 20);

        return response()->json([
            'messages' => $history,
            'total_shown' => count($history),
            'limit' => 20,
        ]);
    }

    /**
     * Clear all AI chat history for the user.
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        AiMessage::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Chat history cleared.']);
    }

    /**
     * Lightweight list of chapters for a book (used by AI widget).
     */
    public function chaptersList(Book $book, Request $request): JsonResponse
    {
        // Ensure ownership
        if ($book->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chapters = $book->chapters()
            ->orderBy('order_number')
            ->get(['id', 'title', 'word_count']);

        return response()->json([
            'chapters' => $chapters,
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ai_provider' => 'required|in:openai,anthropic,google,groq,openrouter',
            'ai_model' => 'required|string|max:100',
            'ai_api_key' => 'required|string|max:500',
        ]);

        $request->user()->update($validated);

        return response()->json(['message' => 'AI settings saved.']);
    }
}
