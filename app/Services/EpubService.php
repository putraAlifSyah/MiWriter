<?php

namespace App\Services;

use App\Models\Book;
use ZipArchive;

class EpubService
{
    public function generate(Book $book): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'epub_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create EPUB file.");
        }

        // Add mimetype (MUST be uncompressed, but ZipArchive in PHP doesn't easily let us set compression method per file without extra flags, but standard epub readers accept it)
        $zip->addFromString('mimetype', 'application/epub+zip');

        // Add META-INF
        $zip->addEmptyDir('META-INF');
        $containerXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
    <rootfiles>
        <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
    </rootfiles>
</container>
XML;
        $zip->addFromString('META-INF/container.xml', $containerXml);

        // Add OEBPS dir
        $zip->addEmptyDir('OEBPS');

        // Fetch chapters
        $chapters = $book->chapters()->orderBy('order_number')->get();

        // Build spine and manifest
        $manifest = '';
        $spine = '';
        $tocNav = '';

        foreach ($chapters as $index => $chapter) {
            $chapterId = 'chapter_' . $index;
            $chapterFile = $chapterId . '.html';
            
            // Generate HTML content
            $content = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{$chapter->title}</title>
    <meta charset="utf-8"/>
    <style>
        body { font-family: serif; line-height: 1.6; padding: 2em; }
        h1 { text-align: center; margin-bottom: 2em; }
    </style>
</head>
<body>
    <h1>{$chapter->title}</h1>
    {$chapter->content_html}
</body>
</html>
HTML;
            $zip->addFromString('OEBPS/' . $chapterFile, $content);

            $manifest .= '<item id="' . $chapterId . '" href="' . $chapterFile . '" media-type="application/xhtml+xml"/>' . "\n";
            $spine .= '<itemref idref="' . $chapterId . '"/>' . "\n";
            
            $playOrder = $index + 1;
            $tocNav .= '<navPoint id="navPoint-' . $playOrder . '" playOrder="' . $playOrder . '">
                <navLabel><text>' . htmlspecialchars($chapter->title) . '</text></navLabel>
                <content src="' . $chapterFile . '"/>
            </navPoint>' . "\n";
        }

        // Build content.opf
        $date = now()->format('Y-m-d');
        $author = htmlspecialchars($book->user->name);
        $title = htmlspecialchars($book->title);

        $opf = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" unique-identifier="BookId" version="2.0">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
        <dc:title>{$title}</dc:title>
        <dc:creator opf:role="aut">{$author}</dc:creator>
        <dc:language>id</dc:language>
        <dc:date>{$date}</dc:date>
        <dc:identifier id="BookId">urn:uuid:miwriter-book-{$book->id}</dc:identifier>
    </metadata>
    <manifest>
        <item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml"/>
        {$manifest}
    </manifest>
    <spine toc="ncx">
        {$spine}
    </spine>
</package>
XML;
        $zip->addFromString('OEBPS/content.opf', $opf);

        // Build toc.ncx
        $ncx = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
    <head>
        <meta name="dtb:uid" content="urn:uuid:miwriter-book-{$book->id}"/>
        <meta name="dtb:depth" content="1"/>
        <meta name="dtb:totalPageCount" content="0"/>
        <meta name="dtb:maxPageNumber" content="0"/>
    </head>
    <docTitle>
        <text>{$title}</text>
    </docTitle>
    <navMap>
        {$tocNav}
    </navMap>
</ncx>
XML;
        $zip->addFromString('OEBPS/toc.ncx', $ncx);

        $zip->close();

        return $tempFile;
    }
}
