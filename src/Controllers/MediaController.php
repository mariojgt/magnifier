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
use Illuminate\Support\Facades\Validator;
use Mariojgt\Magnifier\Support\StorageMode;

class MediaController extends Controller
{
    /** @var MediaFolderController */
    protected $folderManager;
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
        $folderModel = MediaFolder::find($folder);

        if (!$folderModel) {
            return response()->json([
                'message' => 'Folder not found.'
            ], 404);
        }

        // Detect payload too large before Laravel validation (post_max_size/upload_max_filesize)
        $contentLength = (int) $request->server('CONTENT_LENGTH');
        $limitBytes = min(
            $this->parseIniSize(ini_get('post_max_size')),
            $this->parseIniSize(ini_get('upload_max_filesize'))
        );
        if ($contentLength > 0 && $limitBytes > 0 && $contentLength > $limitBytes) {
            return response()->json([
                'message' => 'Payload too large. Increase post_max_size and upload_max_filesize.',
                'detail'  => [
                    'content_length' => $this->formatBytes($contentLength),
                    'server_limit'   => $this->formatBytes($limitBytes),
                    'hints' => [
                        'php.ini: post_max_size, upload_max_filesize',
                        'web server: client_max_body_size (nginx) or LimitRequestBody (Apache)'
                    ]
                ]
            ], 413);
        }

        $request->validate([
            'file' => 'required|mimes:' . config('media.allowed') . '|max:' . config('media.max_size')
        ]);

        // Retrieve file explicitly and handle transport-level errors before validation
        $fileSource = $request->file('file');
        if (!$fileSource) {
            return response()->json([
                'message' => 'No file received. Check client_max_body_size/post_max_size limits.',
            ], 422);
        }
        if (!$fileSource->isValid()) {
            // Map common PHP upload error codes to friendlier messages
            $code = $fileSource->getError();
            $messages = [
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds upload_max_filesize in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified '
                    . 'in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            ];
            $friendly = $messages[$code] ?? 'Unknown upload error (code: '.$code.').';
            return response()->json([
                'message' => 'Invalid upload: ' . $friendly,
            ], 422);
        }

        // Re-run validations with manual Validator so we control messages
        $v = Validator::make($request->all(), [
            'file' => 'required|mimes:' . config('media.allowed') . '|max:' . config('media.max_size'),
        ]);
        if ($v->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $v->errors(),
            ], 422);
        }

        if (!$fileSource || !$fileSource->isValid()) {
            return response()->json([
                'message' => 'Invalid upload payload or file error.'
            ], 422);
        }

    try {
            DB::beginTransaction();

            // Handle the file source and save in the media library and return the media object and the file extension
            // Ensure we pass a concrete MediaFolder model
            if ($folderModel instanceof MediaFolder === false) {
                throw new \InvalidArgumentException('Invalid folder provided.');
            }
            $fileHandle = $this->handleFileSource($fileSource, $folderModel);
            // Guard against extremely large images (by pixel count) to avoid memory issues
            $maxPixels = (int) (config('media.max_pixels') ?? 50000000); // 50 MP default
            if (in_array(strtolower($fileHandle['fileExtension']), ['jpeg','jpg','png','gif','webp'])) {
                $dims = @getimagesize($fileSource->getRealPath());
                if ($dims && isset($dims[0], $dims[1])) {
                    $pixels = (int) $dims[0] * (int) $dims[1];
                    if ($pixels > $maxPixels) {
                        return response()->json([
                            'message' => 'Image too large by pixel count.',
                            'detail' => [
                                'pixels' => $pixels,
                                'max_pixels' => $maxPixels,
                                'dimensions' => $dims[0] . 'x' . $dims[1]
                            ]
                        ], 413);
                    }
                }
            }

            // Set the disk dynamically for this request
            $resolvedDisk = StorageMode::diskFor(StorageMode::current());
            config(['media.disk' => $resolvedDisk]);

            $media      = $this->uploadAction($fileSource, $folderModel, $fileHandle);

            DB::commit();

            $media = $media->fresh();

            if ($isApi) {
                return response()->json([
                    'data' => new MediaResource($media),
                ]);
            } else {
                return $media;
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Media upload failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * This function handle the upload action for when you use a file path or you upload a file
     * @param mixed $fileSource
     * @param mixed $folder
     *
     * @return [type]
     */
    public function uploadAction($fileSource, $folder, $fileHandle)
    {
        // Breakdown the fileHandle Array
        $fileExtension = $fileHandle['fileExtension'];  // File extension
        $finalFileName = $fileHandle['finalFileName'];  // without extension
        $media         = $fileHandle['media'];          // Media object

        // Where we want to save the image
        $pathToSave = rtrim($this->folderManager->media_path, '/') . '/' . ltrim($folder->path, '/');

        // If is a image we going now to resize the image
        if (in_array(strtolower($fileExtension), ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            // Try to decode the image safely; on failure, fallback to non-image path
            try {
                // Make an image intervention object
                $img  = Image::make($fileSource->getRealPath())->orientate();
                // Save the original image height and width
                $media->height = $img->height();
                $media->width  = $img->width();
                $media->save();

                // Loop the config sizes
                // Keep original as backup to avoid cumulative quality loss
                if (method_exists($img, 'backup')) {
                    $img->backup();
                }
                foreach (config('media.sizes') as $key => $mediaSize) {
                    // Build the final file name
                    $finalFile = $finalFileName . '.' . $fileExtension;

                    // For consistent quality across sizes, reset to original before each resize
                    if (method_exists($img, 'reset')) {
                        $img->reset();
                    }
                    // Resize image, with no upscaling, preserving aspect ratio
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
                            $this->handleAwsImageUpload(
                                $img,
                                $finalAbsolutePath,
                                $finalFile,
                                $finalFileName,
                                $media,
                                $key
                            );
                            break;
                        default:
                            throw new \RuntimeException('upload type not allowed');
                    }
                }
            } catch (\Throwable $e) {
                // If not readable as an image, treat it as a generic file upload
                $finalFile     = $finalFileName . '.' . $fileExtension;
                $finalAbsolutePath = $pathToSave . '/' . 'documents' . '/';
                switch (config('media.disk')) {
                    case 'public':
                        $this->handleFilePublicUpload($fileSource, $finalAbsolutePath, $finalFile);
                        break;
                    case 'aws':
                        $this->handleFileAwsUpload($fileSource, $finalAbsolutePath, $finalFile, $media, 'documents');
                        break;
                    default:
                        throw new \RuntimeException('upload type not allowed');
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
                    $this->handleFileAwsUpload($fileSource, $finalAbsolutePath, $finalFile, $media, 'documents');
                    break;
                default:
                    throw new \RuntimeException('upload type not allowed');
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

        // Prefer atomic move when we have an UploadedFile instance
        if (is_object($fileSource) && method_exists($fileSource, 'move')) {
            $fileSource->move($finalAbsolutePath, $finalFile);
        } else {
            // Fallback to copying raw bytes
            if (is_object($fileSource) && method_exists($fileSource, 'getRealPath')) {
                $contents = @file_get_contents($fileSource->getRealPath());
            } elseif (is_string($fileSource) && is_readable($fileSource)) {
                $contents = @file_get_contents($fileSource);
            } else {
                throw new \RuntimeException('Invalid file source for non-image upload.');
            }

            if ($contents === false) {
                throw new \RuntimeException('Failed to read uploaded file contents.');
            }

            File::put($finalAbsolutePath . $finalFile, $contents);
        }

        return true;
    }

    /* AWS IMAGE UPDATE */
    public function handleAwsImageUpload($img, $finalAbsolutePath, $finalFile, $finalFileName, $media, $sizeKey)
    {
        try {
            // Ensure the image is encoded
            $encodedImage = $img->encode();
            $imageContent = $encodedImage->getEncoded();

            // Construct the S3 object key (do not use local absolute paths)
            $s3Key = 'media/' . trim($media->folder->path, '/') . '/' . $sizeKey . '/' . $finalFile;

            // Upload to S3
            $result = Storage::disk('s3')->put($s3Key, $imageContent, [
                'visibility' => 'public',
                'ContentType' => $img->mime(),
            ]);

            if (!$result) {
                throw new \Exception('File upload to S3 failed.');
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('S3 Upload Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mime' => method_exists($img, 'mime') ? $img->mime() : null,
                'bucket' => config('filesystems.disks.s3.bucket'),
            ]);
            throw $e;
        }
    }

    /**
     * Compute a public S3 URL for a given key with fallbacks.
     */
    protected function computeS3Url(string $key): string
    {
        $key = ltrim($key, '/');
        // Prefer configured base URL if available
        $base = config('filesystems.disks.s3.url');
        if (!empty($base)) {
            return rtrim($base, '/') . '/' . $key;
        }
        $bucket = config('filesystems.disks.s3.bucket');
        $endpoint = config('filesystems.disks.s3.endpoint');
        if (!empty($endpoint)) {
            // Custom endpoint (e.g., MinIO)
            return rtrim($endpoint, '/') . '/' . $bucket . '/' . $key;
        }
        // Default AWS URL pattern
        return 'https://' . $bucket . '.s3.amazonaws.com/' . $key;
    }

    /* AWS FILE UPLOAD */
    public function handleFileAwsUpload($fileSource, $finalAbsolutePath, $finalFile, $media, $segment)
    {
        // Build key like media/{folder_path}/{segment}/{filename}
        $s3Key = 'media/' . trim($media->folder->path, '/') . '/' . $segment . '/' . $finalFile;

        // Read contents
        if (is_object($fileSource) && method_exists($fileSource, 'getRealPath')) {
            $path = $fileSource->getRealPath();
            $stream = fopen($path, 'r');
            $result = Storage::disk('s3')->put($s3Key, $stream, ['visibility' => 'public']);
            if (is_resource($stream)) {
                fclose($stream);
            }
        } elseif (is_string($fileSource) && is_readable($fileSource)) {
            $contents = file_get_contents($fileSource);
            $result = Storage::disk('s3')->put($s3Key, $contents, ['visibility' => 'public']);
        } else {
            throw new \RuntimeException('Invalid file source for S3 upload.');
        }

        if (!$result) {
            throw new \RuntimeException('Failed to upload file to S3.');
        }

        return true;
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
        if (!$media) {
            return response()->json(['status' => 'not-found'], 404);
        }
        // Resolve disk dynamically from the current storage mode (header/cache/config),
        // falling back to the media's recorded disk when mode is 'ask'.
        $mode = StorageMode::current();
        if ($mode === StorageMode::ASK) {
            $disk = $media->disk ?: config('media.disk');
        } else {
            $disk = StorageMode::diskFor($mode);
        }
        $isImage = in_array(strtolower($media->extension), ['jpeg', 'jpg', 'png', 'gif', 'webp']);

        if ($disk === 'aws') {
            // Delete from S3 using the same key convention used in uploads
            $base = 'media/' . trim($media->folder->path ?? '', '/') . '/';
            if ($isImage) {
                foreach (config('media.sizes') as $key => $options) {
                    $finalFile = $media->name . '.' . $media->extension;
                    $s3Key = $base . $key . '/' . $finalFile;
                    Storage::disk('s3')->delete($s3Key);
                    if (config('media.use_webp')) {
                        $webpKey = $base . $key . '/' . $media->name . '.webp';
                        Storage::disk('s3')->delete($webpKey);
                    }
                }
            } else {
                $docKey = $base . 'documents/' . $media->name . '.' . $media->extension;
                Storage::disk('s3')->delete($docKey);
            }
        } else {
            // Local/public disk deletion
            $path = rtrim($this->folderManager->media_path, '/') . '/' . ltrim($media->folder->path ?? '', '/') . '/';
            if ($isImage) {
                foreach (config('media.sizes') as $key => $options) {
                    $pathFinalPath = $path . $key . '/';
                    $finalFile     = $media->name . '.' . $media->extension;
                    File::delete($pathFinalPath . $finalFile);
                    if (config('media.use_webp')) {
                        $finalFileWebp = $media->name . '.webp';
                        File::delete($pathFinalPath . $finalFileWebp);
                    }
                }
            } else {
                $finalAbsolutePath = $path . 'documents' . '/';
                $lookingFile = $media->name . '.' . $media->extension;
                File::delete($finalAbsolutePath . $lookingFile);
            }
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

    /**
     * Parse php.ini size strings like "8M", "2G" into bytes.
     */
    protected function parseIniSize($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }
        $unit = strtolower(substr($value, -1));
        $number = (float) substr($value, 0, -1);
        switch ($unit) {
            case 'g':
                return (int) ($number * 1024 * 1024 * 1024);
            case 'm':
                return (int) ($number * 1024 * 1024);
            case 'k':
                return (int) ($number * 1024);
            default:
                return (int) $number;
        }
    }

    /**
     * Format bytes into human readable string.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B','KB','MB','GB','TB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);
        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }

    /**
     * Global media search across all folders.
     * Supports query params: s, orderby (name|created_at|updated_at), order (asc|desc), perPage.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('s', ''));
        $orderBy = in_array($request->query('orderby'), ['name','created_at','updated_at'])
            ? $request->query('orderby')
            : 'created_at';
        $order = strtolower($request->query('order')) === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($request->query('perPage') ?? 24);
        $perPage = max(1, min($perPage, 100));

        $query = Media::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('extension', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('alt', 'like', "%{$q}%")
                    ->orWhere('caption', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $results = $query->orderBy($orderBy, $order)->paginate($perPage);

        return MediaResource::collection($results);
    }
}
