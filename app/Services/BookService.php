<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BookService
{
    public function create(User $user, array $data): Book
    {
        return $user->books()->create([
            'title' => $data['title'],
            'status' => 'draft',
        ]);
    }

    public function update(Book $book, array $data): Book
    {
        $book->update($data);
        return $book->fresh();
    }

    public function delete(Book $book): void
    {
        $book->delete();
    }

    public function uploadCover(Book $book, UploadedFile $file): string
    {
        // Remove existing cover if present
        if ($book->cover_image_path) {
            Storage::disk('public')->delete($book->cover_image_path);
        }
        if ($book->cover_thumbnail_path) {
            Storage::disk('public')->delete($book->cover_thumbnail_path);
        }

        $path = $file->store("covers/{$book->id}", 'public');

        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($path);

        $book->update([
            'cover_image_path' => $path,
            'cover_thumbnail_path' => $thumbnailPath,
        ]);

        return $path;
    }

    public function removeCover(Book $book): void
    {
        if ($book->cover_image_path) {
            Storage::disk('public')->delete($book->cover_image_path);
        }
        if ($book->cover_thumbnail_path) {
            Storage::disk('public')->delete($book->cover_thumbnail_path);
        }

        $book->update([
            'cover_image_path' => null,
            'cover_thumbnail_path' => null,
        ]);
    }

    public function generateThumbnail(string $imagePath): string
    {
        $fullPath = Storage::disk('public')->path($imagePath);
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

        // Thumbnail relative path (same directory as original)
        $directory = pathinfo($imagePath, PATHINFO_DIRNAME);
        $thumbnailRelativePath = $directory . '/thumbnail.' . $extension;
        $thumbnailFullPath = Storage::disk('public')->path($thumbnailRelativePath);

        $image = match (strtolower($extension)) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($fullPath),
            'png' => @imagecreatefrompng($fullPath),
            'webp' => @imagecreatefromwebp($fullPath),
            default => null,
        };

        if ($image) {
            $width = imagesx($image);
            $height = imagesy($image);

            // Scale and center-crop to 300x450
            $targetWidth = 300;
            $targetHeight = 450;
            $targetRatio = $targetWidth / $targetHeight;
            $sourceRatio = $width / $height;

            if ($sourceRatio > $targetRatio) {
                $cropHeight = $height;
                $cropWidth = (int) ($height * $targetRatio);
                $cropX = (int) (($width - $cropWidth) / 2);
                $cropY = 0;
            } else {
                $cropWidth = $width;
                $cropHeight = (int) ($width / $targetRatio);
                $cropX = 0;
                $cropY = (int) (($height - $cropHeight) / 2);
            }

            $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
            imagecopyresampled($thumbnail, $image, 0, 0, $cropX, $cropY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);

            match (strtolower($extension)) {
                'jpg', 'jpeg' => imagejpeg($thumbnail, $thumbnailFullPath, 85),
                'png' => imagepng($thumbnail, $thumbnailFullPath),
                'webp' => imagewebp($thumbnail, $thumbnailFullPath, 85),
                default => null,
            };

            imagedestroy($image);
            imagedestroy($thumbnail);
        }

        return $thumbnailRelativePath;
    }

    public function calculateWordCount(Book $book): int
    {
        return $book->chapters()->sum('word_count');
    }
}
