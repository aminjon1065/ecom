<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Whether the uploaded file can be processed by Intervention Image (raster formats only).
     */
    public function isRaster(UploadedFile $file): bool
    {
        return in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    }

    /**
     * Store an uploaded file optimized as WebP on the public disk.
     *
     * @param  int  $maxWidth  Maximum width in pixels; height is scaled proportionally.
     */
    public function storeOptimized(UploadedFile $file, string $directory, int $maxWidth = 1200): string
    {
        $filename = $directory.'/'.Str::uuid().'.webp';

        $encoded = $this->manager
            ->read($file->getRealPath())
            ->scaleDown(width: $maxWidth)
            ->toWebp(quality: 82);

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    /**
     * Store a thumbnail version of an uploaded file as WebP.
     */
    public function storeThumb(UploadedFile $file, string $directory, int $width = 400, int $height = 400): string
    {
        $filename = $directory.'/'.Str::uuid().'.webp';

        $encoded = $this->manager
            ->read($file->getRealPath())
            ->cover($width, $height)
            ->toWebp(quality: 80);

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }
}
