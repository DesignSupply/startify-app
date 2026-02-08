@extends('layouts.default')

@section('title', 'タグ編集')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>タグ編集</h1>

    @foreach(['name', 'slug'] as $field)
        @error($field)
            <p style="color: red;">{{ $message }}</p>
        @enderror
    @endforeach

    <form method="POST" action="{{ route('tags.update', ['id' => $tag->id]) }}">

        @csrf

        <div>
            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name', $tag->name) }}">
        </div>
        <div>
            <label for="slug">スラッグ</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $tag->slug) }}">
            <small>英数字とハイフンのみ</small>
        </div>
        <div>
            <button type="submit">更新する</button>
            <a href="{{ route('tags.index') }}">戻る</a>
        </div>
    </form>

    @if (!$tag->is_deleted)
        <form method="POST" action="{{ route('tags.destroy', ['id' => $tag->id]) }}" onsubmit="return confirm('本当に削除しますか？');">
            @csrf
            <button type="submit">タグを削除</button>
        </form>
    @else
        <form method="POST" action="{{ route('tags.restore', ['id' => $tag->id]) }}" onsubmit="return confirm('復元しますか？');">
            @csrf
            <button type="submit">タグを復元</button>
        </form>
    @endif

    <a href="{{ route('tags.index') }}">タグ一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection
