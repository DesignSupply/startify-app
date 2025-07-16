<?php

namespace App\Helpers;

class UploadedFileHelper
{
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public static function isImageExtension($extension)
    {
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
    }

    public static function isPreviewableExtension($extension)
    {
        return self::isImageExtension($extension) ||
               in_array(strtolower($extension), ['txt', 'md', 'csv', 'pdf']);
    }
}
