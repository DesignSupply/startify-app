<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // オリジナルファイル名
            $table->string('stored_filename'); // 保存時ファイル名
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->string('file_extension');
            $table->unsignedBigInteger('uploaded_by'); // アップロードユーザーのid（admin_usersテーブルのid）
            $table->text('description')->nullable();
            $table->timestamps();

            // 外部キー制約
            $table->foreign('uploaded_by')->references('id')->on('admin_users');

            // インデックス制約
            $table->index(['uploaded_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
