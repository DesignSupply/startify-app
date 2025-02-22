@extends('layouts.default')

@section('title', 'パスワードリセット・メールアドレス確認')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>パスワードリセット・メールアドレス確認</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @error('email')
        <p style="color: red;">{{ $message }}</p>
    @enderror

    <form method="POST" action="{{ route('password-forgot.post') }}">

        @csrf

        <div>
            <label for="email">登録済みメールアドレス</label>
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
    <a href="{{ route('signin') }}">
        ログイン画面に戻る
    </a>
</main>
@endsection

@section('script_body')
@endsection
