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

    public function book(Request $request, Book $book, \App\Services\EpubService $epubService): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'format' => 'required|in:txt,md,pdf,epub',
        ]);

        $format = $request->format;
        $filename = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $book->title)) . ".{$format}";

        if ($format === 'epub') {
            $epubFile = $epubService->generate($book);
            return response()->download($epubFile, $filename)->deleteFileAfterSend(true);
        }

        if ($format === 'pdf') {
            $html = '<html><head><style>body { font-family: serif; line-height: 1.6; } h1 { text-align: center; page-break-before: always; }</style></head><body>';
            $html .= '<h1 style="margin-top: 40%; font-size: 3em;">' . htmlspecialchars($book->title) . '</h1>';
            $html .= '<p style="text-align: center;">By ' . htmlspecialchars($book->user->name) . '</p>';
            
            foreach ($book->chapters()->orderBy('order_number')->get() as $chapter) {
                $html .= '<h1>' . htmlspecialchars($chapter->title) . '</h1>';
                $html .= $chapter->content_html;
            }
            $html .= '</body></html>';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download($filename);
        }

        $content = $format === 'md'
            ? $this->exportService->exportBookAsMarkdown($book)
            : $this->exportService->exportBookAsText($book);

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
