<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use RuntimeException;

class ImageService
{
    /**
     * Built on first use rather than in the constructor: resolving this
     * service must not fail on a request that never touches an image (saving
     * a product with no new upload, for example).
     */
    protected ?ImageManager $manager = null;

    protected function manager(): ImageManager
    {
        if ($this->manager === null) {
            if (!extension_loaded('gd')) {
                throw new RuntimeException(
                    'Image uploads need the PHP GD extension, which is not enabled on this server. '
                    . 'Enable extension=gd in php.ini and restart PHP.'
                );
            }

            $this->manager = ImageManager::gd();
        }

        return $this->manager;
    }

    /**
     * Compress and store an uploaded image.
     *
     * @param  UploadedFile  $file       The uploaded image file.
     * @param  string        $directory  Storage directory (e.g. 'products', 'collections').
     * @param  int           $quality    JPEG/WebP quality (1-100).
     * @param  int|null      $maxWidth   Scale down if wider than this (preserves aspect ratio).
     * @return string  The stored file path relative to the public disk.
     */
    public function compressAndStore(
        UploadedFile $file,
        string $directory,
        int $quality = 80,
        ?int $maxWidth = 2000,
    ): string {
        $image = $this->manager()->read($file->getRealPath());

        // Scale down large images while preserving aspect ratio
        if ($maxWidth && $image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        // Encode as JPEG for consistent compression
        $encoded = $image->toJpeg($quality);

        // Generate a unique filename
        $filename = $directory . '/' . uniqid() . '_' . time() . '.jpg';

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }
}
