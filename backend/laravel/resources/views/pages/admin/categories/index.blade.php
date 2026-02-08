@extends('layouts.default')

@section('title', 'カテゴリ一覧')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>カテゴリ一覧</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <div>
        <a href="{{ route('categories.create') }}">新規カテゴリ作成</a>
    </div>

    @if (isset($categories) && $categories->count() > 0)
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
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>
                            <a href="{{ route('categories.edit', ['id' => $category->id]) }}">{{ $category->name }}</a>
                        </td>
                        <td>{{ $category->slug }}</td>
                        <td>
                            @if ($category->is_deleted)
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
        <p>カテゴリが存在しません。</p>
    @endif
</main>
@endsection

@section('script_body')
@endsection
