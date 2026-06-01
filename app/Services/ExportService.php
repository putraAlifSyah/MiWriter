<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;

class ExportService
{
    public function exportBookAsText(Book $book): string
    {
        $output = strtoupper($book->title) . "\n\n";

        $chapters = $book->chapters()->orderBy('order_number')->get();

        foreach ($chapters as $chapter) {
            $output .= strtoupper($chapter->title) . "\n\n";
            $output .= $this->htmlToPlainText($chapter->content_html ?? '');
            $output .= "\n\n";
        }

        return rtrim($output);
    }

    public function exportBookAsMarkdown(Book $book): string
    {
        $output = "# {$book->title}\n\n";

        $chapters = $book->chapters()->orderBy('order_number')->get();

        foreach ($chapters as $chapter) {
            $output .= "## {$chapter->title}\n\n";
            $output .= $this->htmlToMarkdown($chapter->content_html ?? '');
            $output .= "\n\n";
        }

        return rtrim($output);
    }

    public function exportChapterAsText(Chapter $chapter): string
    {
        $output = strtoupper($chapter->title) . "\n\n";
        $output .= $this->htmlToPlainText($chapter->content_html ?? '');

        return rtrim($output);
    }

    public function exportChapterAsMarkdown(Chapter $chapter): string
    {
        $output = "# {$chapter->title}\n\n";
        $output .= $this->htmlToMarkdown($chapter->content_html ?? '');

        return rtrim($output);
    }

    public function htmlToPlainText(string $html): string
    {
        if (empty($html)) return '';

        $text = preg_replace('/<(p|div|h[1-6]|blockquote|li)[^>]*>/i', "\n", $html);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    public function htmlToMarkdown(string $html): string
    {
        if (empty($html)) return '';

        $conversions = [
            '/<strong>(.*?)<\/strong>/s' => '**$1**',
            '/<b>(.*?)<\/b>/s' => '**$1**',
            '/<em>(.*?)<\/em>/s' => '*$1*',
            '/<i>(.*?)<\/i>/s' => '*$1*',
            '/<u>(.*?)<\/u>/s' => '$1',
            '/<s>(.*?)<\/s>/s' => '~~$1~~',
            '/<del>(.*?)<\/del>/s' => '~~$1~~',
            '/<h1>(.*?)<\/h1>/s' => "# $1\n",
            '/<h2>(.*?)<\/h2>/s' => "## $1\n",
            '/<h3>(.*?)<\/h3>/s' => "### $1\n",
            '/<blockquote>(.*?)<\/blockquote>/s' => "> $1\n",
            '/<li>(.*?)<\/li>/s' => "- $1\n",
            '/<p>(.*?)<\/p>/s' => "$1\n\n",
            '/<br\s*\/?>/s' => "\n",
        ];

        $markdown = $html;
        foreach ($conversions as $pattern => $replacement) {
            $markdown = preg_replace($pattern, $replacement, $markdown);
        }

        $markdown = strip_tags($markdown);
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown);

        return trim($markdown);
    }
}
