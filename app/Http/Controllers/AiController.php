<?php

namespace App\Http\Controllers;

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
        ]);

        $user = $request->user();
        $book = null;

        if ($request->book_id) {
            $book = Book::where('id', $request->book_id)
                ->where('user_id', $user->id)
                ->first();
        }

        try {
            $response = $this->aiService->ask($user, $request->message, $book);

            return response()->json([
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
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
