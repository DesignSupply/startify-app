@extends('layouts.default')

@section('title', 'ファイル一覧')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ファイル一覧</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <a href="{{ route('admin.files.create') }}">新規ファイルアップロード</a>

    @if ($files->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ファイル名</th>
                    <th>ファイルサイズ</th>
                    <th>アップロード日時</th>
                    <th>アップロードユーザー</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($files as $file)
                    <tr>
                        <td>
                            <a href="{{ route('admin.files.show', ['id' => $file->id]) }}">
                                {{ $file->filename }}
                            </a>
                        </td>
                        <td>{{ $file->human_file_size }}</td>
                        <td>{{ $file->created_at->format('Y年m月d日') }}（{{ $file->created_at->isoFormat('ddd') }}）{{ $file->created_at->format('H:i') }}</td>
                        <td>{{ $file->uploader->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>アップロードされたファイルはありません。</p>
    @endif

    <a href="{{ route('admin.dashboard') }}">ダッシュボードに戻る</a>
</main>
@endsection

@section('script_body')
@endsection
