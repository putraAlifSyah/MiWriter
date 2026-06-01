<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Collection;

class SearchService
{
    public function search(Book $book, string $query, int $limit = 50): array
    {
        if (mb_strlen($query) < 2) {
            return ['results' => [], 'counts' => [], 'total' => 0];
        }

        $results = [
            'chapters' => $this->searchChapters($book, $query),
            'characters' => $this->searchCharacters($book, $query),
            'locations' => $this->searchLocations($book, $query),
            'plot_points' => $this->searchPlotPoints($book, $query),
            'world_elements' => $this->searchWorldElements($book, $query),
        ];

        $allResults = collect();
        foreach ($results as $type => $items) {
            foreach ($items as $item) {
                $allResults->push([
                    'type' => $type,
                    'id' => $item->id,
                    'title' => $this->getItemTitle($item, $type),
                    'snippet' => $this->generateSnippet(
                        $this->getSearchableContent($item, $type),
                        $query
                    ),
                ]);
            }
        }

        $limited = $allResults->take($limit);

        return [
            'results' => $limited->values()->toArray(),
            'counts' => [
                'chapters' => $results['chapters']->count(),
                'characters' => $results['characters']->count(),
                'locations' => $results['locations']->count(),
                'plot_points' => $results['plot_points']->count(),
                'world_elements' => $results['world_elements']->count(),
            ],
            'total' => $allResults->count(),
        ];
    }

    public function searchChapters(Book $book, string $query): Collection
    {
        return $book->chapters()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content_html', 'LIKE', "%{$query}%");
            })
            ->get();
    }

    public function searchCharacters(Book $book, string $query): Collection
    {
        return $book->characters()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('physical_description', 'LIKE', "%{$query}%")
                  ->orWhere('personality_traits', 'LIKE', "%{$query}%")
                  ->orWhere('backstory', 'LIKE', "%{$query}%");
            })
            ->get();
    }

    public function searchLocations(Book $book, string $query): Collection
    {
        return $book->locations()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->get();
    }

    public function searchPlotPoints(Book $book, string $query): Collection
    {
        return $book->plotPoints()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->get();
    }

    public function searchWorldElements(Book $book, string $query): Collection
    {
        return $book->worldElements()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('rules_laws', 'LIKE', "%{$query}%");
            })
            ->get();
    }

    public function generateSnippet(string $content, string $query, int $contextLength = 120): string
    {
        $plainText = strip_tags($content);
        $position = mb_stripos($plainText, $query);

        if ($position === false) {
            return mb_substr($plainText, 0, $contextLength);
        }

        $queryLength = mb_strlen($query);
        $availableContext = $contextLength - $queryLength;
        $beforeContext = (int) floor($availableContext / 2);
        $afterContext = $availableContext - $beforeContext;

        $start = max(0, $position - $beforeContext);
        $end = min(mb_strlen($plainText), $position + $queryLength + $afterContext);

        $snippet = mb_substr($plainText, $start, $end - $start);

        if ($start > 0) $snippet = '...' . $snippet;
        if ($end < mb_strlen($plainText)) $snippet .= '...';

        return $snippet;
    }

    private function getItemTitle($item, string $type): string
    {
        return match ($type) {
            'chapters' => $item->title,
            'characters' => $item->name,
            'locations' => $item->name,
            'plot_points' => $item->title,
            'world_elements' => $item->name,
            default => '',
        };
    }

    private function getSearchableContent($item, string $type): string
    {
        return match ($type) {
            'chapters' => ($item->title ?? '') . ' ' . ($item->content_html ?? ''),
            'characters' => implode(' ', array_filter([
                $item->name, $item->physical_description, $item->personality_traits, $item->backstory,
            ])),
            'locations' => implode(' ', array_filter([$item->name, $item->description])),
            'plot_points' => implode(' ', array_filter([$item->title, $item->description])),
            'world_elements' => implode(' ', array_filter([$item->name, $item->description, $item->rules_laws])),
            default => '',
        };
    }
}
