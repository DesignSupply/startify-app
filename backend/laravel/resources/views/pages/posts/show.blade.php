@extends('layouts.default')

@section('title', '投稿詳細')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>投稿詳細</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <table>
        <tbody>
            <tr>
                <th>ID</th>
                <td>{{ $post->id }}</td>
            </tr>
            <tr>
                <th>タイトル</th>
                <td>{{ $post->title }}</td>
            </tr>
            <tr>
                <th>本文</th>
                <td>{!! nl2br(e($post->body)) !!}</td>
            </tr>
            <tr>
                <th>投稿者</th>
                <td>{{ $post->author }}</td>
            </tr>
            <tr>
                <th>公開日時</th>
                <td>{{ $post->published_at->format('Y年m月d日') }}（{{ $post->published_at->isoFormat('ddd') }}）{{ $post->published_at->format('H:i') }}</td>
            </tr>
            <tr>
                <th>カテゴリ</th>
                <td>
                    @if ($post->categories->count() > 0)
                        {{ $post->categories->pluck('name')->join(', ') }}
                    @else
                        <span>-</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>タグ</th>
                <td>
                    @if ($post->tags->count() > 0)
                        {{ $post->tags->pluck('name')->join(', ') }}
                    @else
                        <span>-</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>作成日時</th>
                <td>{{ $post->created_at->format('Y年m月d日') }}（{{ $post->created_at->isoFormat('ddd') }}）{{ $post->created_at->format('H:i') }}</td>
            </tr>
            <tr>
                <th>更新日時</th>
                <td>{{ $post->updated_at->format('Y年m月d日') }}（{{ $post->updated_at->isoFormat('ddd') }}）{{ $post->updated_at->format('H:i') }}</td>
            </tr>
            <tr>
                <th>削除状態</th>
                <td>
                    @if ($post->is_deleted)
                        <span>削除済み</span>
                    @else
                        <span>-</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    @auth('admin')
        <div>
            <a href="{{ route('posts.edit', ['id' => $post->id]) }}">編集</a>
        </div>
    @endauth

    <a href="{{ route('posts.index') }}">投稿一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection
