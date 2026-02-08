@extends('layouts.default')

@section('title', '投稿編集')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>投稿編集</h1>

    @foreach(['title', 'body', 'published_at'] as $field)
        @error($field)
            <p style="color: red;">{{ $message }}</p>
        @enderror
    @endforeach

    <form method="POST" action="{{ route('posts.update', ['id' => $post->id]) }}">

        @csrf

        <div>
            <label for="title">タイトル</label>
            <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}">
        </div>
        <div>
            <label for="body">本文</label>
            <textarea id="body" name="body" rows="10">{{ old('body', $post->body) }}</textarea>
        </div>
        <div>
            <label for="published_at">公開日時</label>
            <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $post->published_at->format('Y-m-d\TH:i')) }}">
        </div>

        @if (isset($categories) && $categories->count() > 0)
            <div>
                <label>カテゴリ</label>
                @foreach ($categories as $category)
                    <div>
                        <input type="checkbox" id="category_{{ $category->id }}" name="categories[]" value="{{ $category->id }}" {{ in_array($category->id, old('categories', $post->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                        <label for="category_{{ $category->id }}">{{ $category->name }}</label>
                    </div>
                @endforeach
            </div>
        @endif

        @if (isset($tags) && $tags->count() > 0)
            <div>
                <label>タグ</label>
                @foreach ($tags as $tag)
                    <div>
                        <input type="checkbox" id="tag_{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $post->tags->pluck('id')->toArray())) ? 'checked' : '' }}>
                        <label for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                    </div>
                @endforeach
            </div>
        @endif

        <div>
            <button type="submit">更新する</button>
            <a href="{{ route('posts.show', ['id' => $post->id]) }}">戻る</a>
        </div>
    </form>

    @if (!$post->is_deleted)
        <form method="POST" action="{{ route('posts.destroy', ['id' => $post->id]) }}" onsubmit="return confirm('本当に削除しますか？');">
            @csrf
            <button type="submit">投稿を削除</button>
        </form>
    @else
        <form method="POST" action="{{ route('posts.restore', ['id' => $post->id]) }}" onsubmit="return confirm('復元しますか？');">
            @csrf
            <button type="submit">投稿を復元</button>
        </form>
    @endif

    <a href="{{ route('posts.index') }}">投稿一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection
