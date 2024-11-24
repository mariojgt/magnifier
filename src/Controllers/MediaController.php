<?php

namespace Mariojgt\Magnifier\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Mariojgt\Magnifier\Models\Media;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Mariojgt\Magnifier\Models\MediaFolder;
use Mariojgt\Magnifier\Resources\MediaResource;
use Mariojgt\Magnifier\Controllers\MediaFolderController;

class MediaController extends Controller
{
    /**
     * Start the constructor wit the media folder api
     */
    public function __construct()
    {
        $this->folderManager = new MediaFolderController();
    }

    /**
     * Case you are trying to upload from a path, note that $file you need to pass the file as SplFileInfo
     * (EXAMPLE)
     * $fileinfo = new SplFileInfo('/tmp/foo.txt');
     * @param mixed $file
     * @param MediaFolder $folder
     * @param bool $api
     *
     * @return [collection]
     */
    public function uploadPath($file, MediaFolder $folder)
    {
        DB::beginTransaction();
        // Handle the file source and save in the media library and return the media object and the file extension
        $fileHandle = $this->handleFileSource($file, $folder);
        $media      = $this->uploadAction($file, $folder, $fileHandle);
        DB::commit();

        return $media;
    }

    /**
     * This method you upload from the file input
     *
     * @param Request $request
     * @param MediaFolder $folder
     * @param bool $api
     *
     * @return [json]
     */
    public function upload(Request $request, $folder, $isApi = true)
    {
        $folder = MediaFolder::find($folder);

        $request->validate([
            'file' => 'required|mimes:' . config('media.allowed') . '|max:' . config('media.max_size')
        ]);
        $fileSource = $request->file;

        DB::beginTransaction();
        // Handle the file source and save in the media library and return the media object and the file extension
        $fileHandle = $this->handleFileSource($fileSource, $folder);
        $media      = $this->uploadAction($fileSource, $folder, $fileHandle);

        DB::commit();

        $media = $media->fresh();

        if ($isApi) {
            return response()->json([
                'data' => new MediaResource($media),
            ]);
        } else {
            return $media;
        }
    }


    /**
     * This function handle the upload action for when you use a file path or you upload a file
     * @param mixed $fileSource
     * @param mixed $folder
     *
     * @return [type]
     */
    public function uploadAction($fileSource, MediaFolder $folder, $fileHandle)
    {
        // Breakdown the fileHandle Array
        $fileExtension = $fileHandle['fileExtension'];  // File extension
        $finalFileName = $fileHandle['finalFileName'];  // without extension
        $media         = $fileHandle['media'];          // Media object

        // Where is want to save the image
        $pathToSave = $this->folderManager->media_path . '' . $folder->path;

        // If is a image we going now to resize the image
        if (in_array(strtolower($fileExtension), ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            // Make a image intervention object
            $img  = Image::make($fileSource->getRealPath())->orientate();

            // Save the original image height and width
            $media->height = $img->height();
            $media->width  = $img->width();
            $media->save();

            // Loop the config sizes
            foreach (config('media.sizes') as $key => $mediaSize) {
                // Build the final file name
                $finalFile = $finalFileName . '.' . $fileExtension;

                // Resize image, with no upspring, at the same aspect ratio
                $img->resize(
                    $mediaSize['width'],
                    $mediaSize['height'],
                    function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                );
                // Build the path the image will be stored based in the sizes
                $finalAbsolutePath = $pathToSave . '/' . $key . '/';
                // In here we decide the upload type if is public or aws
                switch (config('media.disk')) {
                    case 'public':
                        $this->handleImagePublicUpload($img, $finalAbsolutePath, $finalFile, $finalFileName);
                        break;
                    case 'aws':
                        $this->handleAwsImageUpload($img, $finalAbsolutePath, $finalFile, $finalFileName, $media);
                        break;
                    default:
                        dd('upload type not allowed');
                        break;
                }
            }
        } else {
            // Else is a file just upload to the normal folder
            $finalFile     = $finalFileName . '.' . $fileExtension;
            // Build the path the image will be stored based in the sizes
            $finalAbsolutePath = $pathToSave . '/' . 'documents' . '/';

            // In here we decide the upload type if is public or aws
            switch (config('media.disk')) {
                case 'public':
                    $this->handleFilePublicUpload($fileSource, $finalAbsolutePath, $finalFile);
                    break;
                case 'aws':
                    $this->handleFileAwsUpload($fileSource, $finalAbsolutePath, $finalFile);
                    break;
                default:
                    dd('upload type not allowed');
                    break;
            }
        }

        return $media;
    }

    /* 📁📁 HANDLE THE UPLOAD BASED IN THE TYPES BEGIN 📁📁 */

    /* PUBLIC IMAGE UPLOAD */
    public function handleImagePublicUpload($img, $finalAbsolutePath, $finalFile, $finalFileName)
    {
        // Laravel create folder if not exist
        if (!File::exists($finalAbsolutePath)) {
            File::makeDirectory($finalAbsolutePath, 0777, true);
        }

        // Save the original file
        $img->save($finalAbsolutePath . $finalFile);

        // Create the webp version
        if (config('media.use_webp')) {
            $finalFileWebp = $finalFileName . '.' . 'webp';
            // Save the webp version
            $img->encode('webp', 75)->save($finalAbsolutePath . $finalFileWebp);
        }

        return true;
    }

    /* PUBLIC FILE UPLOAD */
    public function handleFilePublicUpload($fileSource, $finalAbsolutePath, $finalFile)
    {
        // Laravel create folder if not exist
        if (!File::exists($finalAbsolutePath)) {
            File::makeDirectory($finalAbsolutePath, 0777, true);
        }

        // Save the original file
        File::put($finalAbsolutePath . $finalFile, $fileSource);

        return true;
    }

    /* AWS IMAGE UPDATE */
    public function handleAwsImageUpload($img, $finalAbsolutePath, $finalFile, $finalFileName, $media)
    {
        try {
            // Ensure the image is encoded
            $encodedImage = $img->encode();
            $imageContent = $encodedImage->getEncoded();

            // Construct the S3 path
            $s3Path = $finalAbsolutePath . $finalFile;

            // Upload to S3
            $result = Storage::disk('s3')->put($s3Path, $imageContent, [
                'visibility' => 'public',
                'ContentType' => $img->mime(),
            ]);

            if (!$result) {
                throw new \Exception('File upload to S3 failed.');
            }

            // Return success response
            $url = Storage::disk('s3')->url($s3Path);
            return response()->json(['success' => true, 'url' => $url], 200);
        } catch (\Throwable $e) {
            // Log detailed error information
            Log::error('S3 Upload Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                's3Path' => $s3Path ?? null,
                'mime' => $img->mime() ?? null,
                'bucket' => env('AWS_BUCKET'),
            ]);

            // Return error response with detailed message
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* AWS FILE UPLOAD */
    public function handleFileAwsUpload($fileSource, $finalAbsolutePath, $finalFile)
    {
        dd('implement the logic');
    }

    /**
     * This function will get the file upload or by path and return the extension and save in the media
     * @param mixed $fileSource
     * @param mixed $folder
     *
     * @return [type]
     */
    public function handleFileSource($fileSource, $folder)
    {
        // Check if the file source
        switch (class_basename($fileSource)) {
                // Case is a SplFileInfo
            case 'SplFileInfo':
                // Get the name of the file and slug
                $file          = pathinfo($fileSource->getFilename(), PATHINFO_FILENAME);
                $fileExtension = $fileSource->getExtension();
                break;
            default:
                // Get the name of the file and slug
                $file          = pathinfo($fileSource->getClientOriginalName(), PATHINFO_FILENAME);
                $fileExtension = $fileSource->getClientOriginalExtension();
                break;
        }
        // Slug the file name
        $finalFileName = Str::slug($file, '-');

        // Check if the media already exist just in case
        $media = Media::where('name', $finalFileName)->first();
        if (!empty($media)) {
            $media->media_folder_id = $folder->id;
            $media->save();

            return [
                'fileExtension' => $fileExtension,
                'finalFileName' => $finalFileName,
                'media'         => $media,
            ];
        }

        // Create the database file
        $media                  = new Media();
        $media->user_id         = 1000;
        $media->name            = $finalFileName;
        $media->extension       = $fileExtension;
        $media->media_folder_id = $folder->id;
        $media->media_size      = $fileSource->getSize();
        $media->disk            = config('media.disk');
        $media->save();

        return [
            'fileExtension' => $fileExtension,
            'finalFileName' => $finalFileName,
            'media'         => $media,
        ];
    }

    /* 📁📁 HANDLE THE UPLOAD BASED IN THE TYPES END 📁📁*/


    /**
     * ❌❌❌Hand the file delete ❌❌❌
     * @param Media $media
     *
     * @return [true]
     */
    public function mediaDelete($media)
    {
        $media = Media::find($media);
        // Get the folder path
        $path = $this->folderManager->media_path . $media->folder->path . '/';

        // If is a image
        if (in_array($media->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            foreach (config('media.sizes') as $key => $options) {
                // Final path with the size
                $pathFinalPath = $path . $key . '/';
                // The file to delete
                $finalFile     = $media->name . '.' . $media->extension;
                File::delete($pathFinalPath . $finalFile);

                // Create the webp version
                if (config('media.use_webp')) {
                    // The file webp version to delete
                    $finalFileWebp = $media->name . '.' . 'webp';
                    File::delete($pathFinalPath . $finalFileWebp);
                }
            }
        } else {
            // Build the path the image will be stored based in the sizes
            $finalAbsolutePath = $path . 'documents' . '/';
            // File name
            $lookingFile = $media->name . '.' . $media->extension;
            File::delete($finalAbsolutePath . $lookingFile);
        }

        $media->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * 👍👍👍Handle the media update👍👍👍
     * @param Request $request
     * @param Media $media
     *
     * @return [collection]
     */
    public function mediaUpdate(Request $request, $media)
    {
        $media = Media::findOrFail($media);

        $media->title       = Request('title');
        $media->alt         = Request('alt');
        $media->caption     = Request('caption');
        $media->description = Request('description');
        $media->save();

        return $media;
    }
}
