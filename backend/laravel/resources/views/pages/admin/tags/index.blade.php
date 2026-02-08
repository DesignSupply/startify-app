@extends('layouts.default')

@section('title', 'タグ一覧')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>タグ一覧</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <div>
        <a href="{{ route('tags.create') }}">新規タグ作成</a>
    </div>

    @if (isset($tags) && $tags->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>スラッグ</th>
                    <th>削除状態</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tags as $tag)
                    <tr>
                        <td>{{ $tag->id }}</td>
                        <td>
                            <a href="{{ route('tags.edit', ['id' => $tag->id]) }}">{{ $tag->name }}</a>
                        </td>
                        <td>{{ $tag->slug }}</td>
                        <td>
                            @if ($tag->is_deleted)
                                <span>削除済み</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>タグが存在しません。</p>
    @endif

    <a href="{{ route('admin.dashboard') }}">管理者ダッシュボードに戻る</a>
</main>
@endsection

@section('script_body')
@endsection
