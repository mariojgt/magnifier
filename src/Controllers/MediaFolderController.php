<?php

namespace Mariojgt\Magnifier\Controllers;

use Seo;
use File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mariojgt\Magnifier\Models\Media;
use Mariojgt\Magnifier\Models\MediaFolder;
use Mariojgt\Magnifier\Resources\MediaResource;
use Mariojgt\Magnifier\Resources\MediaFolderResource;

class MediaFolderController extends Controller
{
    public function __construct()
    {
        $this->media_path = storage_path('app/public/media/');
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name'      => 'required|unique:media_folders,name|max:255',
        ]);
        $folderName = Str::slug(Request('name'), '-');
        // Create in the database
        $mediaFolder            = new MediaFolder();
        $mediaFolder->name      = $folderName;
        $mediaFolder->parent_id = Request('parent_id') ?? null;
        if (empty(Request('parent_id'))) {
            $mediaFolder->path          = $folderName;
        } else {
            $parent            = MediaFolder::find(Request('parent_id'));
            $mediaFolder->path = $parent->path . '/' . $folderName;
        }

        $mediaFolder->save();

        // Create the folder path
        $path = $this->media_path . $mediaFolder->path;

        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);

        return response()->json([
            'data' => $mediaFolder,
        ]);
    }

    public function deleteFolder(MediaFolder $folder)
    {
        if (!empty($folder->children()[0])) {
            $this->deleteChildren($folder->children());
        }
        $delete = Media::where('media_folder_id', $folder->id)->delete();
        $folder->delete();
        // Remove the files from the storage
        $path = $this->media_path . '' . $folder->path;

        // Check if the path exist
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        return response()->json([
            'data' => true,
        ]);
    }

    public function deleteChildren($children)
    {
        foreach ($children as $key => $value) {
            if (!empty($value->children()[0])) {
                $this->deleteChildren($value->children());
            }

            Media::where('media_folder_id', $value->id)->delete();

            $value->delete();
        }
    }

    public function renameFolder(Request $request, MediaFolder $folder)
    {
        $request->validate([
            'new_name'      => 'required|max:255|unique:media_folders,name',
        ]);

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

        // Fisical path rename
        $oldPath = $this->media_path . '' . $oldPath;
        $newPath = $this->media_path . '' . $newPath;

        rename($oldPath, $newPath);

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

    public function folderChildren(MediaFolder $folder)
    {
        $children = $folder->children();

        return response()->json([
            'children'    => MediaFolderResource::collection($children),
            'parent'      => $folder->parent(),
            'folder_info' => new MediaFolderResource($folder)
        ]);
    }

    public function folderFiles(Request $request, MediaFolder $folder)
    {
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
