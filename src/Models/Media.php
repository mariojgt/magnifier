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

    public function size()
    {
        $bytes = $this->media_size;

        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
