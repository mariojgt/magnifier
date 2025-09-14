<?php

namespace Mariojgt\Magnifier\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class StorageMode
{
    public const LOCAL = 'local';
    public const S3 = 's3';
    public const ASK = 'ask';

    /**
     * Determine the active storage mode for this request.
     * Priority: explicit header > cache > config default
     */
    public static function current(): string
    {
        // Header e.g. X-Magnifier-Mode: local|s3
        $header = Request::header('X-Magnifier-Mode');
        if (in_array($header, [self::LOCAL, self::S3, self::ASK], true)) {
            return $header;
        }

        // Cached preference (UI toggle can persist)
        $cached = Cache::get('magnifier.storage_mode');
        if (in_array($cached, [self::LOCAL, self::S3, self::ASK], true)) {
            return $cached;
        }

        // Fallback to config default
        return Config::get('media.upload_strategy', self::LOCAL);
    }

    /**
     * Map the mode to the Laravel filesystem disk used by Magnifier code.
     */
    public static function diskFor(string $mode): string
    {
        return $mode === self::S3 ? 'aws' : 'public';
    }
}
