<?php

namespace Mariojgt\Magnifier\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Mariojgt\Magnifier\Models\Media;
use Intervention\Image\Facades\Image;
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
		$ext           = pathinfo($file, PATHINFO_EXTENSION);
		$tempFile      = basename($file, "." . $ext);
		$finalFileName = Str::slug($tempFile, '-');

		// Handle the file source and save in the media library and return the media object and the file extension
		$fileHandle = $this->handleFileSource($file, $folder);
		$media      = $this->uploadAction($file, $folder, $fileHandle);

		// Check if the file alread exist if yes do nothing
		// $media = Media::where('name', $finalFileName)->first();
		// if (!empty($media)) {
		//     return $media;
		// }

		// // Create the database file
		// $media                  = new Media();
		// $media->user_id         = admin()->id ?? 1000;
		// $media->name            = $finalFileName;
		// $media->extension       = $file->getExtension();
		// $media->media_folder_id = $folder->id;
		// $media->media_size      = $file->getSize();
		// $media->save();

		// $pathToSave = $this->folderManager->media_path . '' . $folder->path;
		// $finalFile  = $finalFileName . '.' . $file->getExtension();
		// // If is a image need to be resize
		// if (in_array($file->getExtension(), ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
		//     $finalFileWebp  = $finalFileName . '.' . 'webp';
		//     // Make the objecta image intervention object
		//     $img  = Image::make($file->getRealPath())->orientate();
		//     // resize image, with no upsizing, at the same aspect ratio
		//     $img->resize(
		//         intval(config('media.sizes.default.width')),
		//         intval(config('media.sizes.default.height')),
		//         function ($constraint) {
		//             $constraint->aspectRatio();
		//             $constraint->upsize();
		//         }
		//     );
		//     // Save the original file
		//     $img->save($pathToSave . '/' . $finalFile);
		//     // Save the webp version
		//     $img->encode('webp', 75)->save($pathToSave . '/' . $finalFileWebp);
		// } else {
		//     $request->file('file')->move($pathToSave, $finalFile);
		// }

		return $media;
	}

	/**
	 * @param Request $request
	 * @param MediaFolder $folder
	 * @param bool $api
	 *
	 * @return [json]
	 */
	public function upload(Request $request, MediaFolder $folder, $api = true)
	{
		$request->validate([
			'file' => 'required|mimes:' . config('media.allowed') . '|max:' . config('media.max_size')
		]);
		$fileSource = Request('file');

		DB::beginTransaction();
		// Handle the file source and save in the media library and return the media object and the file extension
		$fileHandle = $this->handleFileSource($fileSource, $folder);
		$media      = $this->uploadAction($fileSource, $folder, $fileHandle);

		DB::commit();

		$media = $media->fresh();

		if ($api) {
			return response()->json([
				'data' => new MediaResource($media),
			]);
		} else {
			return $media;
		}
	}


	/**
	 * This fuction handle the upload action for when you use a file path or you upload a file
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

		// If is a image we goin now to resize the image
		if (in_array(strtolower($fileExtension), ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
			// Loop the config sizes
			foreach (config('media.sizes') as $key => $mediaSize) {
				// Build the final file name
				$finalFile = $finalFileName . '.' . $fileExtension;

				// Make a image intervension object
				$img  = Image::make($fileSource->getRealPath())->orientate();

				// Resize image, with no upsizing, at the same aspect ratio
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
						$this->handleAwsImageUpload($img, $finalAbsolutePath, $finalFile, $finalFileName);
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
		$fileSource->move($finalAbsolutePath, $finalFile);
		return true;
	}

	/* AWS IMAGE UPDATE */
	public function handleAwsImageUpload($img, $finalAbsolutePath, $finalFile, $finalFileName)
	{
		dd('implement the logic');
	}

	/* AWS FILE UPLOAD */
	public function handleFileAwsUpload($fileSource, $finalAbsolutePath, $finalFile)
	{
		dd('implement the logic');
	}

	/**
	 * This fuction will get the file upload or by path and retrn the extension and save in the media
	 * @param mixed $fileSource
	 * @param mixed $folder
	 *
	 * @return [type]
	 */
	public function handleFileSource($fileSource, $folder)
	{
		// Get the file class type if is SplFileInfo we handle in a differents way
		if (class_basename($fileSource) == 'SplFileInfo') {
			// Get the name of the file and slug
			$file          = pathinfo($fileSource->getFilename(), PATHINFO_FILENAME);
			$fileExtension = $fileSource->getExtension();
		} else {
			// Get the name of the file and slug
			$file          = pathinfo($fileSource->getClientOriginalName(), PATHINFO_FILENAME);
			$fileExtension = $fileSource->getClientOriginalExtension();
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
		$media->user_id         = admin()->id ?? 1000;
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
	public function mediaDelete(Media $media)
	{
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
    public function mediaUpdate(Request $request, Media $media)
    {

        $media->title       = Request('title');
        $media->alt         = Request('alt');
        $media->caption     = Request('caption');
        $media->description = Request('description');
        $media->save();

        return $media;
    }
}
