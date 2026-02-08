@extends('layouts.default')

@section('title', '投稿一覧')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>投稿一覧</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @auth('admin')
        <div>
            <a href="{{ route('posts.create') }}">新規投稿作成</a>
        </div>
    @endauth

    @if (isset($posts) && $posts->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>タイトル</th>
                    <th>投稿者</th>
                    <th>公開日時</th>
                    <th>削除状態</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posts as $post)
                    <tr>
                        <td>
                            <a href="{{ route('posts.show', ['id' => $post->id]) }}">{{ $post->id }}</a>
                        </td>
                        <td>
                            <a href="{{ route('posts.show', ['id' => $post->id]) }}">{{ $post->title }}</a>
                        </td>
                        <td>{{ $post->author }}</td>
                        <td>{{ $post->published_at->format('Y年m月d日 H:i') }}</td>
                        <td>
                            @if ($post->is_deleted)
                                <span>削除済み</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $posts->links() }}
    @else
        <p>投稿が存在しません。</p>
    @endif
</main>
@endsection

@section('script_body')
@endsection
