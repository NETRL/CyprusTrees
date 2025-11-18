<?php

namespace App\Jobs;

use App\Models\Photo;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Throwable;

class ProcessPhotoUpload implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $photoId) {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');

        $photo = Photo::find($this->photoId);
        if (!$photo || !$photo->path) {
            Log::warning('ProcessPhotoUpload early return: missing photo or path', [
                'photo_id' => $this->photoId,
                'exists'   => (bool) $photo,
                'path'     => $photo?->path,
            ]);
            return;
        }

        $fullPath = Storage::disk('public')->path($photo->path);

        try {
            // 1. Convert to JPG + auto-orient + downscale
            $this->normalizeImage($fullPath);

            // Updafe the file extention
            $pathInfo = pathinfo($photo->path);
            if (strtolower($pathInfo['extension']) !== 'jpg') {
                $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.jpg';

                // Update file path in storage
                Storage::disk('public')->move($photo->path, $newPath);
                $fullPath = Storage::disk('public')->path($newPath);

                // Update database
                $photo->path = $newPath;
                $photo->save();
            }


            // 2. Optimize via Spatie
            $optimizer = OptimizerChainFactory::create();
            $optimizer->optimize($fullPath);



            // 3. Update URL + status
            $photo->update([
                'url'    => Storage::disk('public')->url($photo->path),
                'status' => 'ready',
            ]);

            Log::info('ProcessPhotoUpload finished', [
                'photo_id' => $this->photoId,
                'url'      => $photo->url,
                'status'   => $photo->status,
            ]);
        } catch (\Throwable $e) {
            $photo->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('ProcessPhotoUpload failed', [
                'photo_id' => $this->photoId,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function normalizeImage(string $path): void
    {
        // Detect image type and load accordingly
        $imageType = exif_imagetype($path);

        $image = match ($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new \Exception('Unsupported image format'),
        };

        if (!$image) {
            throw new \Exception('Failed to load image');
        }

        // Handle EXIF orientation (critical for mobile photos)
        $image = $this->autoRotateImage($image, $path);

        $width  = imagesx($image);
        $height = imagesy($image);

        // Downscale if needed
        $maxDim = 2560;
        $ratio  = min($maxDim / $width, $maxDim / $height, 1);

        if ($ratio < 1) {
            $newW = (int)($width * $ratio);
            $newH = (int)($height * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);

            // Preserve quality during resize
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        // Convert to JPG and save (this handles PNG/WebP → JPG conversion)
        imagejpeg($image, $path, 90);
        imagedestroy($image);
    }

    private function autoRotateImage($image, string $path)
    {
        // Only JPEG files have EXIF data
        if (exif_imagetype($path) !== IMAGETYPE_JPEG) {
            Log::warning("Image is not JPEG, cannot get exif data. Returning...");
            return $image;
        }

        $exif = @exif_read_data($path);
        if (!$exif || empty($exif['Orientation'])) {
            Log::warning("Orientation not found in EXIF data. Returning...");
            return $image;
        }

        $angle = match ($exif['Orientation']) {
            3 => 180,
            6 => 270,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated !== false) {
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }

    public function failed(\Throwable $exception)
    {
        $photo = Photo::find($this->photoId);
        if ($photo) {
            $photo->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
