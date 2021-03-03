<?php

namespace AdminUI\AdminUIAdmin\Controllers\Media;

//Laravel standard classes
use Seo;
use File;
use Illuminate\Http\Request;
// MODELS
use App\Http\Controllers\Controller;
use AdminUI\AdminUIAdmin\Models\Media;
use AdminUI\AdminUIAdmin\Models\MediaFolder;
use AdminUI\AdminUIAdmin\Helpers\ImageHelper;
use Facades\AdminUI\AdminUIAdmin\Facades\BootFacade;
use AdminUI\AdminUIAdmin\Resources\MediaFolderResource;
use AdminUI\AdminUIAdmin\Controllers\Media\MediaFolderApiController;
use Illuminate\Support\Str;
use AdminUI\AdminUIAdmin\Resources\MediaResource;
use Response;
use Image;

class MediaApiController extends Controller
{

    public function __construct()
    {
        $this->folderManager = new MediaFolderApiController();
    }


    public function upload(Request $request, MediaFolder $folder)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlx,xls,pdf,jpeg,png,gif,webp|max:2048'
        ]);

        $file          = pathinfo(Request('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $finalFileName = Str::slug($file, '-');

        // Create the database file
        $media                  = new Media();
        $media->user_id         = admin()->id;
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
            //$img->save($pathToSave . '/' . $finalFile);
            // Save the webp version
            $img->encode('webp', 75)->save($pathToSave . '/' . $finalFileWebp);
        } else {
            $request->file('file')->move($pathToSave, $finalFile);
        }

        return response()->json([
            'data' => new MediaResource($media),
        ]);
    }

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

    public function resizeImage($media, $size)
    {
        // Final filename
        $fileName     = $media->name . '-' . $size . '.webp';
        $pathToSave   = $this->folderManager->media_path . '' . $media->folder->path;
        $originalFile = $pathToSave . '/' . $media->name . '.' . $media->extension;

        // Make the objecta image intervention object
        $img  = Image::make($originalFile)->orientate();
        // resize image, with no upsizing, at the same aspect ratio
        $avaliablesizes = [
            'default',
            'medium',
            'small',
            'tiny',
            'thumbnail',
        ];
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
}
