<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(private ExportService $exportService)
    {
    }

    public function book(Request $request, Book $book): StreamedResponse
    {
        $request->validate([
            'format' => 'required|in:txt,md',
        ]);

        $format = $request->format;
        $content = $format === 'md'
            ? $this->exportService->exportBookAsMarkdown($book)
            : $this->exportService->exportBookAsText($book);

        $filename = str_replace(' ', '_', $book->title) . ".{$format}";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => $format === 'md' ? 'text/markdown' : 'text/plain',
        ]);
    }

    public function chapter(Request $request, Chapter $chapter): StreamedResponse
    {
        $request->validate([
            'format' => 'required|in:txt,md',
        ]);

        $format = $request->format;
        $content = $format === 'md'
            ? $this->exportService->exportChapterAsMarkdown($chapter)
            : $this->exportService->exportChapterAsText($chapter);

        $filename = str_replace(' ', '_', $chapter->title) . ".{$format}";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => $format === 'md' ? 'text/markdown' : 'text/plain',
        ]);
    }
}
