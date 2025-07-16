<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Helpers\UploadedFileHelper;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_extension',
        'uploaded_by',
        'description'
    ];

    public function uploader()
    {
        return $this->belongsTo(AdminUser::class, 'uploaded_by');
    }

    public function getHumanFileSizeAttribute()
    {
        return UploadedFileHelper::formatBytes($this->file_size);
    }

    public function isImage()
    {
        return UploadedFileHelper::isImageExtension($this->file_extension);
    }

    public function isPreviewable()
    {
        return UploadedFileHelper::isPreviewableExtension($this->file_extension);
    }

    // モデルが持つのは適切なメソッド（サービスクラスの呼び出しの糖衣構文）
    public function generateThumbnail($width = 300)
    {
        return app(\App\Services\UploadedFileService::class)->generateThumbnail($this, $width);
    }
}
