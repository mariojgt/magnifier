<?php

namespace Mariojgt\Magnifier\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    public $fillable = ['name'];

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function children()
    {
        return MediaFolder::where('parent_id', $this->id)->get();
    }

    public function parent()
    {
        $path = explode('/', $this->path);
        $breadCrumb = [];
        foreach ($path as $key => $value) {
            $tempFolder = MediaFolder::where('name', $value)->first();
            $breadCrumb[] = [
                'id'   => $tempFolder->id,
                'name' => $tempFolder->name
            ];
        }
        return $breadCrumb;
    }
}
