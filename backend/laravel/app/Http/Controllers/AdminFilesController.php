<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminFilesController extends Controller
{

    public function index()
    {
        $files = UploadedFile::with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.admin.files.index', compact('files'));
    }

    public function create()
    {
        return view('pages.admin.files.create');
    }

    public function store(FileUploadRequest $request)
    {
        $file = $request->file('upload_file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // 一意のファイル名を生成
        $storedName = Str::uuid() . '.' . $extension;

        // ファイルをuploadsディスクに保存
        $filePath = $file->storeAs('', $storedName, 'uploads');

        // データベースに保存
        $uploadedFile = UploadedFile::create([
            'filename' => $originalName,
            'stored_filename' => $storedName,
            'file_path' => $filePath,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'file_extension' => $extension,
            'uploaded_by' => Auth::guard('admin')->id(),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.files.show', $uploadedFile->id)
            ->with('status', 'ファイルがアップロードされました。');
    }

    public function show($id)
    {
        $file = UploadedFile::with('uploader')->findOrFail($id);

        return view('pages.admin.files.show', compact('file'));
    }

    public function edit($id)
    {
        $file = UploadedFile::with('uploader')->findOrFail($id);

        return view('pages.admin.files.edit', compact('file'));
    }

    public function update(FileUpdateRequest $request, $id)
    {
        $file = UploadedFile::findOrFail($id);

        $file->update([
            'description' => $request->description,
        ]);

        return redirect()->route('admin.files.show', $file->id)
            ->with('status', 'ファイル情報が更新されました。');
    }

    public function destroy($id)
    {
        $file = UploadedFile::findOrFail($id);

        // ストレージからファイルを削除
        if (Storage::disk('uploads')->exists($file->file_path)) {
            Storage::disk('uploads')->delete($file->file_path);
        }

        // データベースから削除
        $file->delete();

        return redirect()->route('admin.files.index')
            ->with('status', 'ファイルが削除されました。');
    }

    public function download($id)
    {
        $file = UploadedFile::findOrFail($id);

        if (!Storage::disk('uploads')->exists($file->file_path)) {
            abort(404, 'ファイルが見つかりません。');
        }

        return Storage::disk('uploads')->download($file->file_path, $file->filename);
    }
}
