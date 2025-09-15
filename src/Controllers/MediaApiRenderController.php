<?php

namespace Mariojgt\Magnifier\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mariojgt\Magnifier\Controllers\MediaFolderController;
use Mariojgt\Magnifier\Support\StorageMode;

class MediaApiRenderController extends Controller
{
    /** @var MediaFolderController */
    protected $folderManager;
    /**
     * Start the construct with the media folder controller
     */
    public function __construct()
    {
        $this->folderManager = new MediaFolderController();
    }

    /**
     * Render the file or image
     * Always tries AWS first, falls back to public storage if file doesn't exist on AWS
     *
     * @param mixed $media
     * @param bool $useFallback
     *
     * @return array Returns array with 'urls' and 'storage_type' keys
     */
    public function renderMediaUrlPath($media, $useFallback = false)
    {
        // First, try AWS storage
        if ($this->fileExistsOnAws($media)) {
            return [
                'urls' => $this->handleAwsFile($media, $useFallback),
                'storage_type' => 's3',
                'is_s3' => true
            ];
        }

        // If file doesn't exist on AWS, fallback to public storage
        return [
            'urls' => $this->renderPublicFile($media, $useFallback),
            'storage_type' => 'public',
            'is_s3' => false
        ];
    }

    /**
     * Check if file exists on AWS S3
     *
     * @param mixed $media
     * @return bool
     */
    protected function fileExistsOnAws($media): bool
    {
        // Check if any of the expected files exist on S3
        if (in_array($media->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            // For images, check if at least one size variant exists
            foreach (config('media.sizes') as $key => $size) {
                $finalFile = $media->name . '.' . $media->extension;
                $s3Key = 'media/' . trim($media->folder->path, '/') . '/' . $key . '/' . $finalFile;

                if (Storage::disk('s3')->exists($s3Key)) {
                    return true;
                }
            }
        } else {
            // For documents, check the documents folder
            $finalFile = $media->name . '.' . $media->extension;
            $s3Key = 'media/' . trim($media->folder->path, '/') . '/documents/' . $finalFile;

            if (Storage::disk('s3')->exists($s3Key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render the public image
     * @param mixed $media
     *
     * @return [type]
     */
    public function renderPublicFile($media, $useFallback)
    {
        $masterPath = '/storage/media/' . $media->folder->path . '/';
        if (in_array($media->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            foreach (config('media.sizes') as $key => $size) {
                // Build the file name
                $finalFile     = $media->name . '.' . $media->extension;
                // Build the path of the storate using the url
                $path = $masterPath . $key . '/' . $finalFile;

                // Check if the file exists if is using the fallback
                if ($useFallback == true) {
                    // Path to check if the files exist
                    $pathToCheck = $this->folderManager->media_path . '' . $media->folder->path;
                    // Build the path the image will be stored based in the sizes
                    $finalAbsolutePath = $pathToCheck . '/' . $key . '/' . $finalFile;
                    if (!File::exists($finalAbsolutePath)) {
                        $url[$key] = $this->getFallbackImage();
                    } else {
                        $url[$key] = url($path);
                    }
                } else {
                    $url[$key] = url($path);
                }
            }
        } else {
            $path = $masterPath . 'documents/' . $media->name . '.' . $media->extension;
            $url['default'] = url($path);
        }

        return $url;
    }

    /**
     * Handle AWS S3 file rendering
     * @param mixed $media
     * @param mixed $useFallback
     *
     * @return array
     */
    public function handleAwsFile($media, $useFallback)
    {
        // Use S3 key convention used on upload: media/{folder_path}/{size}/{file}
        if (in_array($media->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            foreach (config('media.sizes') as $key => $size) {
                $finalFile = $media->name . '.' . $media->extension;
                $s3Key = 'media/' . trim($media->folder->path, '/') . '/' . $key . '/' . $finalFile;

                if ($useFallback === true && !Storage::disk('s3')->exists($s3Key)) {
                    $url[$key] = $this->getFallbackImage();
                } else {
                    $url[$key] = $this->computeS3Url($s3Key);
                }
            }
        } else {
            $finalFile = $media->name . '.' . $media->extension;
            $s3Key = 'media/' . trim($media->folder->path, '/') . '/documents/' . $finalFile;
            $url['default'] = $this->computeS3Url($s3Key);
        }

        return $url;
    }


    /**
     * Render the image fallback
     *
     * @return [type]
     */
    public function getFallbackImage()
    {
        return url('images' . config('media.img_fall_back'));
    }

    /**
     * Compute a public S3 URL for a given key with fallbacks.
     */
    protected function computeS3Url(string $key): string
    {
        $key = ltrim($key, '/');
        $base = config('filesystems.disks.s3.url');
        if (!empty($base)) {
            return rtrim($base, '/') . '/' . $key;
        }
        $bucket = config('filesystems.disks.s3.bucket');
        $endpoint = config('filesystems.disks.s3.endpoint');
        if (!empty($endpoint)) {
            return rtrim($endpoint, '/') . '/' . $bucket . '/' . $key;
        }
        return 'https://' . $bucket . '.s3.amazonaws.com/' . $key;
    }
}
