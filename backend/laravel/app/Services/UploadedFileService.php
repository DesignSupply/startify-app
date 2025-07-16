<?php

namespace App\Services;

use App\Models\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class UploadedFileService
{
    public function generateThumbnail(UploadedFile $file, int $width = 300): ?string
    {
        if (!$file->isImage()) {
            return null;
        }

        try {
            // ストレージからファイルを読み込み
            $fileContent = Storage::disk('uploads')->get($file->file_path);

            // ImageManagerを初期化
            $manager = new ImageManager(new Driver());

            // 画像を読み込み
            $image = $manager->read($fileContent);

            // 幅を指定サイズにリサイズ（アスペクト比維持）
            $image->scaleDown($width, null);

            // base64エンコードして返す
            return 'data:' . $file->mime_type . ';base64,' . base64_encode($image->encode());

        } catch (\Exception $e) {
            // エラーが発生した場合はnullを返す
            return null;
        }
    }

    public function deleteFile(UploadedFile $file): bool
    {
        try {
            // ストレージからファイルを削除
            Storage::disk('uploads')->delete($file->file_path);

            // データベースからレコードを削除
            return $file->delete();
        } catch (\Exception $e) {
            return false;
        }
    }
}
