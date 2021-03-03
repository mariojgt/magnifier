<?php

namespace Mariojgt\Magnifier\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray($request)
    {
        $url = [];
        if (in_array($this->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            $url[] = route('admin.media.render', ['media' => $this->id, 'size' => 'default']);
            $url[] = route('admin.media.render', ['media' => $this->id, 'size' => 'medium']);
            $url[] = route('admin.media.render', ['media' => $this->id, 'size' => 'small']);
            $url[] = route('admin.media.render', ['media' => $this->id, 'size' => 'tiny']);
            $url[] = route('admin.media.render', ['media' => $this->id, 'size' => 'thumbnail']);
        } else {
            $url[] = route('admin.media.render', ['media' => $this->id, 'size' => 'default']);
        }

        return [
            'id'          => $this->id,
            'user'        => $this->admin,
            'url'         => $url,
            'title'       => $this->title,
            'alt'         => $this->alt,
            'caption'     => $this->caption,
            'description' => $this->description,
            'ext'         => $this->extension,
            'media_size'  => $this->media_size,
            'media_key'   => short_encrypt($this->id),
        ];
    }
}
