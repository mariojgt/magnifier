<?php

namespace Mariojgt\Magnifier\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Mariojgt\Magnifier\Controllers\MediaController;

class MediaResource extends JsonResource
{
    public function toArray($request)
    {
        $url = [];
        if (in_array($this->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            $url[] = route('media.render', ['media' => $this->id, 'size' => 'default']);
            $url[] = route('media.render', ['media' => $this->id, 'size' => 'medium']);
            $url[] = route('media.render', ['media' => $this->id, 'size' => 'small']);
            $url[] = route('media.render', ['media' => $this->id, 'size' => 'tiny']);
            $url[] = route('media.render', ['media' => $this->id, 'size' => 'thumbnail']);
        } else {
            $url[] = route('media.render', ['media' => $this->id, 'size' => 'default']);
        }

        // Public Media Render urls
        $urlPublic    = [];
        $mediaManager = new MediaController();
        if (in_array($this->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            $urlPublic[] = $mediaManager->mediaRenderPublic($this->id, 'default');
            $urlPublic[] = $mediaManager->mediaRenderPublic($this->id, 'medium');
            $urlPublic[] = $mediaManager->mediaRenderPublic($this->id, 'small');
            $urlPublic[] = $mediaManager->mediaRenderPublic($this->id, 'tiny');
            $urlPublic[] = $mediaManager->mediaRenderPublic($this->id, 'thumbnail');
        } else {
            $urlPublic[] = $mediaManager->mediaRenderPublic($this->id, 'default');
        }

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'user'            => $this->admin,
            'url'             => $url,
            'url_cache_media' => $urlPublic,
            'title'           => $this->title,
            'alt'             => $this->alt,
            'caption'         => $this->caption,
            'description'     => $this->description,
            'ext'             => $this->extension,
            'media_size'      => $this->size(),
            'media_key'       => encrypt($this->id),
            'created_at'      => $this->created_at->diffForHumans(),
            'updated_at'      => $this->updated_at->diffForHumans(),
        ];
    }
}
