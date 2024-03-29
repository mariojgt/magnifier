<?php

namespace Mariojgt\Magnifier\Models;

use Illuminate\Database\Eloquent\Model;
use Mariojgt\Magnifier\Controllers\MediaApiRenderController;

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

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Return the paths of the files so we can use in the website
     * @return [type]
     */
    public function renderFiles($useFallback = false)
    {
        $mediaManager = new MediaApiRenderController();
        return $mediaManager->renderMediaUrlPath($this, $useFallback);
    }
}
