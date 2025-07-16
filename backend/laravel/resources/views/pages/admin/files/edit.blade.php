@extends('layouts.default')

@section('title', 'ファイル編集')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ファイル編集</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p style="color: red;">{{ $error }}</p>
            @endforeach
        </div>
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
                <th>アップロード日時</th>
                <td>{{ $file->created_at->format('Y年m月d日') }}（{{ $file->created_at->isoFormat('ddd') }}）{{ $file->created_at->format('H:i') }}</td>
            </tr>
        </tbody>
    </table>

    <form method="POST" action="{{ route('admin.files.update', ['id' => $file->id]) }}">

        @csrf

        <div>
            <label for="description">ファイル説明</label>
            <textarea id="description" name="description" rows="4">{{ old('description', $file->description) }}</textarea>
        </div>
        <div>
            <button type="submit">更新</button>
            <a href="{{ route('admin.files.show', ['id' => $file->id]) }}">戻る</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.files.destroy', ['id' => $file->id]) }}" onsubmit="return confirm('本当に削除しますか？');">

        @csrf

        <button type="submit">ファイルを削除</button>
    </form>
    <a href="{{ route('admin.files.show', ['id' => $file->id]) }}">詳細画面に戻る</a>
</main>
@endsection

@section('script_body')
@endsection
