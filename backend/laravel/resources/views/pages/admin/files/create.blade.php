@extends('layouts.default')

@section('title', 'ファイルアップロード')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ファイルアップロード</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p style="color: red;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="upload_file">ファイルを選択</label>
            <input type="file" id="upload_file" name="upload_file" required>
        </div>

        <div>
            <label for="description">ファイル説明</label>
            <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
        </div>

        <div>
            <button type="submit">アップロード</button>
            <a href="{{ route('admin.files.index') }}">戻る</a>
        </div>
    </form>

    <a href="{{ route('admin.files.index') }}">ファイル一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection
