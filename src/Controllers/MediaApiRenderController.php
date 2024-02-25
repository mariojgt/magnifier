<?php

namespace Mariojgt\Magnifier\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Mariojgt\Magnifier\Controllers\MediaFolderController;

class MediaApiRenderController extends Controller
{
    /**
     * Start the construct with the media folder controller
     */
    public function __construct()
    {
        $this->folderManager = new MediaFolderController();
    }

    /**
     * Render the file or image
     *
     * @param mixed $media
     * @param bool $useFallback
     *
     * @return [type]
     */
    public function renderMediaUrlPath($media, $useFallback = false)
    {
        switch (config('media.disk')) {
            case 'public':
                return $this->renderPublicFile($media, $useFallback);
                break;
            case 'aws':
                $this->handleAwsFile($media, $useFallback);
                break;
            default:
                dd('upload type not allowed');
                break;
        }
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
     * Aws file handler
     * @param mixed $media
     * @param mixed $useFallback
     *
     * @return [type]
     */
    public function handleAwsFile($media, $useFallback)
    {
        dd('implement login to aws');
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
}
