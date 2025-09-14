<?php

namespace Mariojgt\Magnifier\Controllers;

use File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mariojgt\Magnifier\Models\Media;
use Illuminate\Support\Facades\Storage;
use Mariojgt\Magnifier\Models\MediaFolder;
use Mariojgt\Magnifier\Resources\MediaResource;
use Mariojgt\Magnifier\Resources\MediaFolderResource;

class MediaFolderController extends Controller
{
    /**
     * Absolute base path on disk where media folders/files are stored.
     */
    public $media_path;

    public function __construct()
    {
        $this->media_path = storage_path(config('media.default_folder'));
    }

    public function createFolder(Request $request)
    {
        // Validate the data
        $request->validate([
            'name'      => 'required|unique:media_folders,name|max:255',
        ]);

        $mediaFolder =  $this->makeFolder(Request('name'), Request('parent_id'));

        // Return the response
        return response()->json([
            'data' => $mediaFolder,
        ]);
    }

    public function makeFolder($name, $parent_id = null)
    {
        $mediaFolder = MediaFolder::where('name', $name)->first();

        if (!empty($mediaFolder)) {
            return $mediaFolder;
        } else {
            // Parse the folder name to make sure the name is valid
            $folderName = Str::slug($name, '-');
            // Create in the database
            $mediaFolder            = new MediaFolder();
            $mediaFolder->name      = $folderName;
            $mediaFolder->parent_id = $parent_id ?? null;
            // Check if the folder being create has a parent
            if (empty($parent_id)) {
                $mediaFolder->path          = $folderName;
            } else {
                $parent            = MediaFolder::find($parent_id);
                $mediaFolder->path = $parent->path . '/' . $folderName;
            }
            // Save the folder
            $mediaFolder->save();

            // Create the folder path
            $path = $this->media_path . $mediaFolder->path;
            // CHeck if need to create a fisical directory on the server
            File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
            // Return the response
            return $mediaFolder;
        }
    }

    public function deleteFolder($folder)
    {
        $folder   = MediaFolder::find($folder);

        // Check if the folder has children to delete
        if (!empty($folder->children()[0])) {
            $this->deleteChildren($folder->children());
        }
        // Delete all the media with the folder id
        $delete = Media::where('media_folder_id', $folder->id)->delete();
        // Delete the folder
        $folder->delete();
        // Remove the files from the storage
        $path = $this->media_path . '' . $folder->path;

        // Check if the path exist
        if (File::isDirectory($path)) {
            // If yes delete the folder itself
            File::deleteDirectory($path);
        }
        // Return a response
        return response()->json([
            'data' => true,
        ]);
    }

    public function deleteChildren($children)
    {
        // Do a recursive function that will loop and delete the files under the folder
        foreach ($children as $key => $value) {
            if (!empty($value->children()[0])) {
                $this->deleteChildren($value->children());
            }

            Media::where('media_folder_id', $value->id)->delete();

            $value->delete();
        }
    }

    public function renameFolder(Request $request, $folder)
    {
        $folder   = MediaFolder::find($folder);

        // Validate the data
        $request->validate([
            'new_name'      => 'required|max:255|unique:media_folders,name',
        ]);

        // Make sure is a valid name
        $newName = Str::slug(Request('new_name'), '-');
        // Get the old path
        $oldPath = $folder->path;
        // Get the new path
        if (empty($folder->parent_folder)) {
            $newPath = $newName;
        } else {
            $parent  = MediaFolder::find($folder->parent_folder);
            $newPath = $parent->path . '/' . $newName;
        }

        if (!empty($folder->children())) {
            $this->renameChildren($folder->children(), $oldPath, $newPath);
        }

        $folder->name = $newName;
        $folder->path = $newPath;
        $folder->save();

        // Fiscal path rename
        $oldPath = $this->media_path . '' . $oldPath;
        $newPath = $this->media_path . '' . $newPath;
        // Check if the path exist using Storage
        if (File::isDirectory($oldPath)) {
            // Copy the folder to the new path
            File::copyDirectory($oldPath, $newPath);
            // Delete the old folder
            File::deleteDirectory($oldPath);
        }

        return response()->json([
            'data' => $newName,
        ]);
    }

    public function renameChildren($children, $oldPath, $newPath)
    {
        foreach ($children as $key => $value) {
            $updatedPath = str_replace($oldPath, $newPath, $value->path);
            $value->path = $updatedPath;
            $value->save();
            if (!empty($value->children()[0])) {
                $this->renameChildren($value->children(), $oldPath, $newPath);
            }
        }
    }

    public function folderList()
    {
        $parents = MediaFolder::whereNull('parent_id')->get();

        return response()->json([
            'data' => MediaFolderResource::collection($parents),
        ]);
    }

    public function folderChildren($folder)
    {
        $folder   = MediaFolder::find($folder);
        $children = $folder->children();

        return response()->json([
            'children'    => MediaFolderResource::collection($children),
            'parent'      => $folder->parent(),
            'folder_info' => new MediaFolderResource($folder)
        ]);
    }

    public function folderFiles(Request $request, $folder)
    {
        $folder = MediaFolder::find($folder);
        $dataReturn = null;
        $order_by   = 'id';
        $order      = 'DESC';
        $paginate   = request('per_page') ?? 10;

        // Check if the orderby column was passed
        if (!empty(Request('orderby'))) {
            $order_by = Request('orderby');
        }

        // Check if the order parameter was passed
        if (!empty(Request('order'))) {
            $order = Request('order');
        }

        // Check if items per page parameter was passed
        if (!empty(Request('perPage'))) {
            $paginate = Request('perPage');
        }

        $folder = $folder->media();

        // Check if we are sending any search
        if (!empty(Request('s'))) {
            $folder = $folder->where('name', 'like', '%' . Request('s') . '%');
        }

        $folder = $folder->orderBy($order_by, $order)->paginate($paginate);

        return MediaResource::collection($folder);
    }
}
