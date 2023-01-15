<?php

namespace Mariojgt\Magnifier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelMedia extends Model
{
    use HasFactory;


    // Fillable
    protected $fillable = [
        'model_id',
        'model_type',
        'media_id',
    ];

    // Return the media
    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
