@extends('layouts.default')

@section('title', 'パスワードリセット・パスワード再設定')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>パスワードリセット・パスワード再設定</h1>

    @error('password')
        <p style="color: red;">{{ $message }}</p>
    @enderror

    <form method="POST" action="{{ route('password-reset.post') }}">

        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
        <div>
            <label for="password">新しいパスワード</label>
            <input
                type="password"
                name="password"
                id="password"
                required
            >
        </div>
        <div>
            <label for="password_confirmation">新しいパスワード（確認用）</label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                required
            >
        </div>
        <div>
            <button type="submit">
                パスワードを再設定する
            </button>
        </div>
    </form>
    <a href="{{ route('signin') }}">
        ログイン画面に戻る
    </a>
</main>
@endsection

@section('script_body')
@endsection
