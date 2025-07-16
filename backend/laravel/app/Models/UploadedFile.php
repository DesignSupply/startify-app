<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    protected $fillable = [
        'filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_extension',
        'uploaded_by',
        'description',
    ];

    /**
     * アップロードユーザーとのリレーション
     */
    public function uploader()
    {
        return $this->belongsTo(AdminUser::class, 'uploaded_by');
    }

    /**
     * ファイルサイズを人間が読みやすい形式に変換
     */
    public function getHumanFileSizeAttribute()
    {
        return $this->formatBytes($this->file_size);
    }

    /**
     * バイトを人間が読みやすい形式に変換
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * 画像ファイルかどうかを判定
     */
    public function isImage()
    {
        return in_array($this->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
    }

    /**
     * プレビュー可能なファイルかどうかを判定
     */
    public function isPreviewable()
    {
        return $this->isImage() || in_array($this->file_extension, ['txt', 'md', 'csv', 'pdf']);
    }
}
