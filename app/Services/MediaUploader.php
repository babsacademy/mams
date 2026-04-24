<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaUploader
{
    private readonly ImageConverter $imageConverter;

    public function __construct()
    {
        $this->imageConverter = new ImageConverter;
    }

    /**
     * Upload a file (image or video) and return the Media model.
     *
     * @throws \RuntimeException
     */
    public function upload(TemporaryUploadedFile $file): Media
    {
        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());

        \Log::info('MediaUploader::upload started', [
            'size' => $file->getSize(),
            'mime' => $realMime,
        ]);

        if ($realMime === 'image/svg+xml') {
            return $this->uploadSvg($file);
        } elseif ($this->isImage($realMime)) {
            return $this->uploadImage($file, $realMime);
        } elseif ($this->isVideo($realMime)) {
            return $this->uploadVideo($file, $realMime);
        }

        throw new \RuntimeException("Type de fichier non supporté : {$realMime}");
    }

    private function uploadSvg(TemporaryUploadedFile $file): Media
    {
        $filename = Str::random(32).'.svg';
        $storagePath = 'media/images/'.$filename;

        Storage::disk('public')->put($storagePath, $file->get());

        return Media::create([
            'filename' => $filename,
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.svg',
            'path' => $storagePath,
            'disk' => 'public',
            'size' => Storage::disk('public')->size($storagePath),
            'width' => null,
            'height' => null,
            'type' => 'image',
            'mime_type' => 'image/svg+xml',
        ]);
    }

    private function uploadImage(TemporaryUploadedFile $file, string $mimeType): Media
    {
        $tmpPath = $file->getRealPath();
        $webpPath = $this->imageConverter->toWebP($tmpPath);
        $dimensions = $this->imageConverter->getDimensions($webpPath);

        $filename = Str::random(32).'.webp';
        $storagePath = 'media/images/'.$filename;

        Storage::disk('public')->put($storagePath, file_get_contents($webpPath));
        unlink($webpPath);

        return Media::create([
            'filename' => $filename,
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.webp',
            'path' => $storagePath,
            'disk' => 'public',
            'size' => Storage::disk('public')->size($storagePath),
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'type' => 'image',
            'mime_type' => 'image/webp',
        ]);
    }

    private function uploadVideo(TemporaryUploadedFile $file, string $mimeType): Media
    {
        $extension = $this->getVideoExtension($mimeType);
        $filename = Str::random(32).'.'.$extension;
        $storagePath = 'media/videos/'.$filename;

        \Log::info('MediaUploader: Starting video store', [
            'original_name' => $file->getClientOriginalName(),
            'temp_path' => $file->getRealPath(),
            'target_path' => $storagePath,
            'size' => $file->getSize(),
        ]);

        try {
            $path = Storage::disk('public')->putFileAs('media/videos', $file, $filename);

            if (! $path) {
                throw new \RuntimeException('Failed to store video file on public disk.');
            }

            \Log::info('MediaUploader: Video stored successfully', ['path' => $path]);

            $size = Storage::disk('public')->size($path);
            \Log::info('MediaUploader: Video size confirmed', ['stored_size' => $size]);

            return Media::create([
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'size' => $size,
                'width' => null,
                'height' => null,
                'type' => 'video',
                'mime_type' => $mimeType,
                'duration' => null,
                'thumbnail_path' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('MediaUploader: Video upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function isImage(string $mimeType): bool
    {
        return in_array($mimeType, [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    private function isVideo(string $mimeType): bool
    {
        return in_array($mimeType, [
            'video/mp4',
            'video/webm',
            'video/quicktime',
        ]);
    }

    private function getVideoExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/quicktime' => 'mov',
            default => 'mp4',
        };
    }
}
