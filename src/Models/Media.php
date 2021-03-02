<?php

namespace Mariojgt\Magnifier\Models;

use Storage;
use Illuminate\Database\Eloquent\Model;
use AdminUI\AdminUIAdmin\Helpers\ImageHelper;

class Media extends Model
{
    public $protected = ['id'];

    protected $table = 'media';

    public function folder()
    {
        return $this->hasOne(MediaFolder::class, 'id', 'media_folder_id');
    }

    public function getSizeAttribute()
    {
        $filename = $this->id.'.'.$this->extension;
        $path     = config('media.default_folder').'large/'.$filename;
        $exists   = Storage::disk(config('media.disk'))->exists($path);
        $bytes    = $exists ? Storage::disk(config('media.disk'))->size($path) : 0;
        $kb       = ceil($bytes / 1024);
        return $kb.'kb';
    }

    public function path()
    {
        $imageHelper = new ImageHelper();
        return $imageHelper->imageLink($this);
    }
}
