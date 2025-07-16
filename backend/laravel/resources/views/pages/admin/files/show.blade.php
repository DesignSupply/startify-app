@extends('layouts.default')

@section('title', 'ファイル詳細')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ファイル詳細</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>ファイル情報</h2>
    <table>
        <tbody>
            <tr>
                <th>ファイル名</th>
                <td>{{ $file->filename }}</td>
            </tr>
            <tr>
                <th>ファイルサイズ</th>
                <td>{{ $file->human_file_size }}</td>
            </tr>
            <tr>
                <th>ファイル形式</th>
                <td>{{ $file->mime_type }}</td>
            </tr>
            <tr>
                <th>拡張子</th>
                <td>{{ $file->file_extension }}</td>
            </tr>
            <tr>
                <th>アップロード日時</th>
                <td>{{ $file->created_at->format('Y年m月d日') }}（{{ $file->created_at->isoFormat('ddd') }}）{{ $file->created_at->format('H:i') }}</td>
            </tr>
            <tr>
                <th>アップロードユーザー</th>
                <td>{{ $file->uploader->name }}</td>
            </tr>
            <tr>
                <th>説明</th>
                <td>{{ $file->description ? $file->description : '（説明なし）' }}</td>
            </tr>
        </tbody>
    </table>

    @if ($file->isImage())
        <h2>プレビュー</h2>
        <p>※画像のプレビュー機能は今後実装予定です</p>
        <p>プレビューを表示するには、<a href="{{ route('admin.files.download', ['id' => $file->id]) }}">ダウンロード</a>してご確認ください。</p>
    @endif

    <div>
        <a href="{{ route('admin.files.edit', ['id' => $file->id]) }}">編集</a>
        <a href="{{ route('admin.files.download', ['id' => $file->id]) }}">ダウンロード</a>
    </div>

    <a href="{{ route('admin.files.index') }}">ファイル一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection
