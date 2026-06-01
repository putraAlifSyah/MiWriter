<?php

namespace App\Services;

use App\Models\Chapter;

class EditorService
{
    public function saveContent(Chapter $chapter, array $delta, string $html): Chapter
    {
        $html = $this->stripUnsupportedFormatting($html);
        $chapterService = new ChapterService();

        return $chapterService->updateContent($chapter, $delta, $html);
    }

    public function stripUnsupportedFormatting(string $html): string
    {
        // Allowed tags: b, strong, i, em, u, s, del, h1-h3, blockquote, ol, ul, li, p, br
        $allowedTags = '<b><strong><i><em><u><s><del><h1><h2><h3><blockquote><ol><ul><li><p><br>';

        // Strip all attributes from allowed tags
        $html = preg_replace('/<(\w+)\s[^>]*>/i', '<$1>', $html);

        return strip_tags($html, $allowedTags);
    }
}
