<?php

namespace Mariojgt\Magnifier\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Mariojgt\Magnifier\Controllers\MediaController;

class MediaResource extends JsonResource
{
    public function toArray($request)
    {
        $fileData = $this->renderFiles(true);

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'user'         => $this->admin,
            'url'          => $fileData['urls'] ?? $fileData, // Handle both old and new format
            'title'        => $this->title,
            'alt'          => $this->alt,
            'caption'      => $this->caption,
            'description'  => $this->description,
            'ext'          => $this->extension,
            'media_size'   => $this->size(),
            'media_key'    => encrypt($this->id),
            'storage_type' => $fileData['storage_type'] ?? 'unknown',
            'is_s3'        => $fileData['is_s3'] ?? false,
            'created_at'   => $this->created_at->diffForHumans(),
            'updated_at'   => $this->updated_at->diffForHumans(),
        ];
    }
}
