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
                <th>アップロード日時</th>
                <td>{{ $file->created_at->format('Y年m月d日') }}（{{ $file->created_at->isoFormat('ddd') }}）{{ $file->created_at->format('H:i') }}</td>
            </tr>
            <tr>
                <th>アップロードユーザー</th>
                <td>{{ $file->uploader->name }}</td>
            </tr>
            <tr>
                <th>説明</th>
                <td>{{ $file->description ? $file->description : '-' }}</td>
            </tr>
            <tr>
                <th>プレビュー</th>

                @if ($file->isImage() && $thumbnail)
                    <td>
                        <img src="{{ $thumbnail }}" alt="{{ $file->filename }}" class="max-w-full h-auto" style="max-width: 300px;">
                    </td>
                @elseif ($file->isImage())
                    <td>
                        <p>画像プレビューの生成に失敗しました</p>
                    </td>
                @else
                    <td>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000" viewBox="0 0 16 16">
                            <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                            <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                        </svg>
                        <span>このファイルはプレビュー表示できません</span>
                    </td>
                @endif

            </tr>
        </tbody>
    </table>
    <div>
        <a href="{{ route('admin.files.edit', ['id' => $file->id]) }}">編集</a>
        <a href="{{ route('admin.files.download', ['id' => $file->id]) }}">ダウンロード</a>
    </div>
    <a href="{{ route('admin.files.index') }}">ファイル一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection
