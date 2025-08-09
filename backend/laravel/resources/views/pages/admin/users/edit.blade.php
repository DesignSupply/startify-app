@extends('layouts.default')

@section('title', 'ユーザー編集')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ユーザー編集</h1>

    @foreach(['name', 'email', 'password', 'password_confirmation'] as $field)
        @error($field)
            <p style="color: red;">{{ $message }}</p>
        @enderror
    @endforeach

    <form method="POST" action="{{ route('admin.users.update', ['id' => $user->id]) }}">

        @csrf

        <div>
            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}">
        </div>
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}">
        </div>
        <div>
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password">
        </div>
        <div>
            <label for="password_confirmation">パスワード（確認）</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
        </div>
        <div>
            <button type="submit">更新する</button>
            <a href="{{ route('admin.users.show', ['id' => $user->id]) }}">戻る</a>
        </div>
    </form>

    @if (!$user->is_deleted)
        <form method="POST" action="{{ route('admin.users.destroy', ['id' => $user->id]) }}" onsubmit="return confirm('本当に削除しますか？');">
            @csrf
            <button type="submit">ユーザーを削除</button>
        </form>
    @else
        <form method="POST" action="{{ route('admin.users.restore', ['id' => $user->id]) }}" onsubmit="return confirm('復元しますか？');">
            @csrf
            <button type="submit">ユーザーを復元</button>
        </form>
    @endif

    <a href="{{ route('admin.users.index') }}">ユーザー一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection


