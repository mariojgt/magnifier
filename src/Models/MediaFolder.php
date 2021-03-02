<?php

namespace Mariojgt\Magnifier\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    public $fillable = ['name'];

    public function children()
    {
        return MediaFolder::where('parent_id', $this->id)->get();
    }
}
