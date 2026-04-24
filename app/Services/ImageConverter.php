<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;

class ImageConverter
{
    public function __construct(
        private readonly int $quality = 85
    ) {}

    /**
     * Convert an image file to WebP and return the output path.
     *
     * @throws RuntimeException
     */
    public function toWebP(string $sourcePath): string
    {
        $mime = mime_content_type($sourcePath);

        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => $this->createFromPng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => throw new RuntimeException("Format non supporté : {$mime}"),
        };

        if (! $image) {
            throw new RuntimeException('Impossible de lire l\'image source.');
        }

        $outputPath = sys_get_temp_dir().'/'.Str::random(16).'.webp';

        try {
            imagewebp($image, $outputPath, $this->quality);
        } finally {
            unset($image);
        }

        return $outputPath;
    }

    /**
     * @return array{width: int, height: int}
     */
    public function getDimensions(string $path): array
    {
        [$width, $height] = getimagesize($path);

        return ['width' => (int) $width, 'height' => (int) $height];
    }

    private function createFromPng(string $path): \GdImage
    {
        $source = imagecreatefrompng($path);

        // Preserve transparency
        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);

        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);
        unset($source);

        return $canvas;
    }
}
