<?php

namespace Mariojgt\Magnifier\Resources;

use AdminUI\AdminUIAdmin\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFolderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'parent_id' => $this->parent_id,
            'count'     => $this->children()->count(),
            'children'  => $this->children(),
        ];
    }
}
