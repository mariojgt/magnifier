<?php

namespace Mariojgt\Magnifier\Models;

use Illuminate\Database\Eloquent\Model;
use Mariojgt\Magnifier\Controllers\MediaApiRenderController;

class Media extends Model
{
    public $protected = ['id'];
    protected $table = 'media';

    // Add storage_type to fillable if you use mass assignment
    protected $fillable = [
        'user_id',
        'name',
        'extension',
        'media_folder_id',
        'media_size',
        'disk',
        'storage_type',
        'title',
        'alt',
        'caption',
        'description',
        'height',
        'width'
    ];

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
     * @param bool $useFallback
     * @return array
     */
    public function renderFiles($useFallback = false)
    {
        $mediaManager = new MediaApiRenderController();
        return $mediaManager->renderMediaUrlPath($this, $useFallback);
    }

    /**
     * Return the proxy paths of the files
     * @return array
     */
    public function renderProxyFiles()
    {
        $mediaManager = new MediaApiRenderController();
        return $mediaManager->renderMediaUrlPath($this, false, true);
    }

    /**
     * Get proxy URL for a specific size
     * @param string $size
     * @return string
     */
    public function getProxyUrl($size = null)
    {
        $filename = $this->name . '.' . $this->extension;

        // For images, use the specified size or 'original' as default
        if (in_array($this->extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
            $size = $size ?? 'original';
        } else {
            // For documents, always use 'documents'
            $size = 'documents';
        }

        return route('media.proxy', [
            'mediaId' => $this->id,
            'size' => $size,
            'filename' => $filename
        ]);
    }

    /**
     * Check if media is stored on S3
     * @return bool
     */
    public function isS3Storage()
    {
        return in_array($this->storage_type, ['s3', 'aws']);
    }

    /**
     * Check if media is stored locally/publicly
     * @return bool
     */
    public function isLocalStorage()
    {
        return in_array($this->storage_type, ['public', 'local']);
    }

    /**
     * Get storage type display name
     * @return string
     */
    public function getStorageTypeDisplayName()
    {
        $names = [
            'public' => 'Public Storage',
            'local' => 'Local Storage',
            's3' => 'Amazon S3',
            'aws' => 'Amazon S3',
        ];

        return $names[$this->storage_type] ?? ucfirst($this->storage_type);
    }
}
