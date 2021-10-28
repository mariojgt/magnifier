<?php

namespace Mariojgt\Magnifier\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Mariojgt\Magnifier\Models\Media;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Response;
use Mariojgt\Magnifier\Models\MediaFolder;
use Mariojgt\Magnifier\Resources\MediaResource;
use Mariojgt\Magnifier\Controllers\MediaFolderController;

class MediaController extends Controller
{
    public function __construct()
    {
        // Get the media sizes
        $sizes = [];
        // If not empty for some reason
        if (config('media.sizes')) {
            foreach (config('media.sizes') as $key => $value) {
                $sizes[] = $key;
            }
        }
        $this->sizes         = $sizes;

        $this->folderManager = new MediaFolderController();
    }

    /**
     * @param mixed $file
     * @param MediaFolder $folder
     * @param bool $api
     *
     * @return [collection]
     */
    public function uploadPath($file, MediaFolder $folder, $api = true)
    {
        $finalFileName = Str::slug($file->getFilename(), '-');

        // Create the database file
        $media                  = new Media();
        $media->user_id         = Auth::user()->id ?? 1000;
        $media->name            = $finalFileName;
        $media->extension       = $file->getExtension();
        $media->media_folder_id = $folder->id;
        $media->media_size      = $file->getSize();
        $media->save();

        $pathToSave = $this->folderManager->media_path . '' . $folder->path;
        $finalFile  = $finalFileName . '.' . $file->getExtension();
        // If is a image need to be resize
        if (in_array($file->getExtension(), ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            $finalFileWebp  = $finalFileName . '.' . 'webp';
            // Make the objecta image intervention object
            $img  = Image::make($file->getRealPath())->orientate();
            // resize image, with no upsizing, at the same aspect ratio
            $img->resize(
                intval(config('media.sizes.default.width')),
                intval(config('media.sizes.default.height')),
                function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );
            // Save the original file
            $img->save($pathToSave . '/' . $finalFile);
            // Save the webp version
            $img->encode('webp', 75)->save($pathToSave . '/' . $finalFileWebp);
        } else {
            // $request->file('file')->move($pathToSave, $finalFile);
        }

        return $media;
    }

    // Upload media logic
    public function upload(Request $request, MediaFolder $folder)
    {
        $request->validate([
            'file' => 'required|mimes:' . config('media.allowed_extensions') . '|max:2048'
        ]);

        $file          = pathinfo(Request('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $finalFileName = Str::slug($file, '-');

        // Create the database file
        $media                  = new Media();
        //$media->user_id         = admin()->id;
        $media->name            = $finalFileName;
        $media->extension       = Request('file')->getClientOriginalExtension();
        $media->media_folder_id = $folder->id;
        $media->media_size      = Request('file')->getSize();
        $media->save();

        $pathToSave = $this->folderManager->media_path . '' . $folder->path;
        $finalFile  = $finalFileName . '.' . Request('file')->getClientOriginalExtension();

        // If is a image need to be resize
        if (in_array(Request('file')->getClientOriginalExtension(), ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            $finalFileWebp  = $finalFileName . '.' . 'webp';
            // Make the objecta image intervention object
            $img  = Image::make(Request('file')->getRealPath())->orientate();
            // resize image, with no upsizing, at the same aspect ratio
            $img->resize(
                intval(config('media.sizes.default.width')),
                intval(config('media.sizes.default.height')),
                function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            );
            // Save the original file
            $img->save($pathToSave . '/' . $finalFile);
            // Save the webp version
            $img->encode('webp', 75)->save($pathToSave . '/' . $finalFileWebp);
        } else {
            $request->file('file')->move($pathToSave, $finalFile);
        }

        return response()->json([
            'data' => new MediaResource($media),
        ]);
    }

    // Render any kind of media
    public function mediaRender(Media $media, $size = 'default')
    {
        // Get the path
        $path = $this->folderManager->media_path;
        // Check if is a image if yes resize to the target file
        if (in_array($media->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            // Get the file
            if ($size == 'default') {
                $lookingFile = $media->name . '.webp';
            } else {
                $lookingFile = $media->name . '-' . $size . '.webp';
            }
            if (!File::exists($path . $media->folder->path . '/' . $lookingFile)) {
                $this->resizeImage($media, $size);
            }
        } else {
            $lookingFile = $media->name . '.' . $media->extension;
        }

        $finalPath = $path . $media->folder->path . '/' . $lookingFile;

        $file = File::get($finalPath);
        $type = File::mimeType($finalPath);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    public function mediaRenderPublic($media, $size = 'default')
    {
        $media = Media::findOrFail($media);
        // Get the path
        $path = $this->folderManager->media_path;
        // Check if is a image if yes resize to the target file
        if (in_array($media->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            // Get the file
            if ($size == 'default') {
                $lookingFile = $media->name . '.webp';
            } else {
                $lookingFile = $media->name . '-' . $size . '.webp';
            }
            if (!File::exists($path . $media->folder->path . '/' . $lookingFile)) {
                $this->resizeImage($media, $size);
            }
        } else {
            $lookingFile = $media->name . '.' . $media->extension;
        }

        // Original file in the storage
        $storageFile = $path . $media->folder->path . '/' . $lookingFile;
        // Basce public path
        $cachePublicPath = 'cache_media/' . $media->folder->path;
        $basePubliPath = public_path($cachePublicPath);
        // Final path witl the file ready to be copy
        $publicFile = $basePubliPath . '/' . $lookingFile;

        // Check if the files exist if yes return the path
        if (File::exists($publicFile)) {
            return url($cachePublicPath . '/' . $lookingFile);
        } else {
            File::isDirectory($basePubliPath) or File::makeDirectory($basePubliPath, 0777, true, true);
            File::copy($storageFile, $publicFile);
            return url($cachePublicPath . '/' . $lookingFile);
        }
    }

    // Auto image resize
    public function resizeImage($media, $size)
    {
        // Final filename
        $fileName     = $media->name . '-' . $size . '.webp';
        $pathToSave   = $this->folderManager->media_path . '' . $media->folder->path;
        $originalFile = $pathToSave . '/' . $media->name . '.' . $media->extension;

        // Make the objecta image intervention object
        $img  = Image::make($originalFile)->orientate();

        // resize image, with no upsizing, at the same aspect ratio
        $avaliablesizes = $this->sizes;

        if (in_array($size, $avaliablesizes)) {
            $targetSize = config('media.sizes')[$size];
        } else {
            $targetSize = config('media.sizes')['small'];
        }

        // Resize the image
        $img->resize(
            intval($targetSize['width']),
            intval($targetSize['height']),
            function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            }
        );
        // Save the webp version
        $img->encode('webp', 75)->save($pathToSave . '/' . $fileName);
    }

    public function mediaDelete(Media $media)
    {
        // Get the path
        $path = $this->folderManager->media_path . $media->folder->path . '/';
        // Check if is a image
        if (in_array($media->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            // Get media avaliable sizes
            $avaliablesizes = $this->sizes;

            foreach ($avaliablesizes as $key => $size) {
                if ($size == 'default') {
                    // Get the orinal file
                    $lookingFile = $media->name . '.' . $media->extension;
                    File::delete($path . $lookingFile);
                    // Geth the webp file version
                    $lookingFile = $media->name . '.webp';
                    File::delete($path . $lookingFile);
                } else {
                    $fileName     = $media->name . '-' . $size . '.webp';
                    File::delete($path . $fileName);
                }
            }
        } else {
            $lookingFile = $media->name . '.' . $media->extension;
            File::delete($path . $lookingFile);
        }

        $media->delete();

        return true;
    }

    public function mediaUpdate(Request $request, Media $media)
    {
        // $request->validate([
        //     'title'       => 'required|max:255',
        //     'alt'         => 'required|max:255',
        //     'caption'     => 'required|max:255',
        //     'description' => 'required|max:255',
        // ]);

        $media->title       = Request('title');
        $media->alt         = Request('alt');
        $media->caption     = Request('caption');
        $media->description = Request('description');
        $media->save();

        return $media;
    }
}
