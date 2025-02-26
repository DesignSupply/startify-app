@extends('layouts.default')

@section('title', '管理者パスワードリセット・メールアドレス確認')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>管理者パスワードリセット・メールアドレス確認</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @error('email')
        <p style="color: red;">{{ $message }}</p>
    @enderror

    <form method="POST" action="{{ route('admin.password-forgot.request') }}">

        @csrf

        <div>
            <label for="email">登録済み管理者メールアドレス</label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
            >
        </div>
        <div>
            <button type="submit">
                送信する
            </button>
        </div>
    </form>
    <a href="{{ route('admin') }}">
        管理者ログイン画面に戻る
    </a>
</main>
@endsection

@section('script_body')
@endsection
