<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

    public function uploader()
    {
        return $this->belongsTo(AdminUser::class, 'uploaded_by');
    }

    public function getHumanFileSizeAttribute()
    {
        return $this->formatBytes($this->file_size);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function isImage()
    {
        return in_array($this->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
    }

    public function isPreviewable()
    {
        return $this->isImage() || in_array($this->file_extension, ['txt', 'md', 'csv', 'pdf']);
    }

    public function generateThumbnail($width = 300)
    {
        if (!$this->isImage()) {
            return null;
        }

        try {
            // ストレージからファイルを読み込み
            $fileContent = Storage::disk('uploads')->get($this->file_path);

            // ImageManagerを初期化
            $manager = new ImageManager(new Driver());

            // 画像を読み込み
            $image = $manager->read($fileContent);

            // 幅を300pxにリサイズ（アスペクト比維持）
            $image->scaleDown($width, null);

            // base64エンコードして返す
            return 'data:' . $this->mime_type . ';base64,' . base64_encode($image->encode());

                } catch (\Exception $e) {
            // エラーが発生した場合はnullを返す
            return null;
        }
    }
}
