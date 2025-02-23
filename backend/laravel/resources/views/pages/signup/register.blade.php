@extends('layouts.default')

@section('title', '新規ユーザー登録')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>新規ユーザー登録</h1>

    @foreach(['email', 'password'] as $field)
        @error($field)
            <p style="color: red;">{{ $message }}</p>
        @enderror
    @endforeach

    <form method="POST" action="{{ route('signup.register.post') }}">

        @csrf

        <div>
            <label for="email">メールアドレス</label>
            <span>{{ $email }}</span>
        </div>
        <div>
            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="password_confirmation">パスワード（確認用）</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>
        <div>
            <button type="submit">登録</button>
        </div>
    </form>
</main>
@endsection

@section('script_body')
@endsection
