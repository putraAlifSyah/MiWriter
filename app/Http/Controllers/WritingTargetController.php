<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\WritingTarget;
use App\Services\WritingTargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WritingTargetController extends Controller
{
    public function __construct(private WritingTargetService $targetService)
    {
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:daily,weekly',
            'word_count' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $max = $request->type === 'daily' ? 100000 : 500000;
                    if ($value > $max) {
                        $fail("The {$request->type} target must not exceed {$max}.");
                    }
                },
            ],
        ]);

        $target = $this->targetService->setTarget(
            $book,
            $request->user(),
            $validated['type'],
            $validated['word_count']
        );

        return response()->json([
            'message' => 'Writing target set.',
            'target' => $target,
        ], 201);
    }

    public function update(Request $request, Book $book, WritingTarget $target): JsonResponse
    {
        $validated = $request->validate([
            'word_count' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($target) {
                    $max = $target->type === 'daily' ? 100000 : 500000;
                    if ($value > $max) {
                        $fail("The {$target->type} target must not exceed {$max}.");
                    }
                },
            ],
        ]);

        $target->update(['word_count' => $validated['word_count']]);

        return response()->json([
            'message' => 'Writing target updated.',
            'target' => $target,
        ]);
    }
}
