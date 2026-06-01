<?php

namespace App\Services;

use App\Models\Book;
use App\Models\PlotPoint;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlotService
{
    public function create(Book $book, array $data): PlotPoint
    {
        $nextPosition = $book->plotPoints()->max('position') + 1;
        $data['position'] = $nextPosition;

        return $book->plotPoints()->create($data);
    }

    public function update(PlotPoint $point, array $data): PlotPoint
    {
        $point->update($data);
        return $point->fresh();
    }

    public function delete(PlotPoint $point): void
    {
        DB::transaction(function () use ($point) {
            $book = $point->book;
            $deletedPosition = $point->position;

            $point->delete();

            $book->plotPoints()
                ->where('position', '>', $deletedPosition)
                ->decrement('position');
        });
    }

    public function reorder(Book $book, array $orderedIds): void
    {
        DB::transaction(function () use ($book, $orderedIds) {
            $bookPointIds = $book->plotPoints()->pluck('id')->toArray();
            $diff = array_diff($orderedIds, $bookPointIds);

            if (!empty($diff) || count($orderedIds) !== count($bookPointIds)) {
                throw new InvalidArgumentException('Invalid plot point IDs for reorder');
            }

            foreach ($orderedIds as $index => $pointId) {
                PlotPoint::where('id', $pointId)->update(['position' => $index + 1]);
            }
        });
    }

    public function linkCharacters(PlotPoint $point, array $characterIds): void
    {
        if (count($characterIds) > 20) {
            throw new InvalidArgumentException('Maximum 20 characters per plot point.');
        }

        $point->characters()->sync($characterIds);
    }

    public function linkLocations(PlotPoint $point, array $locationIds): void
    {
        if (count($locationIds) > 10) {
            throw new InvalidArgumentException('Maximum 10 locations per plot point.');
        }

        $point->locations()->sync($locationIds);
    }
}
