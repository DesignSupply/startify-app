@extends('layouts.default')

@section('title', '新規ユーザー登録・メールアドレス確認')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>新規ユーザー登録・メールアドレス確認</h1>

    @error('email')
        <p style="color: red;">{{ $message }}</p>
    @enderror

    <form method="POST" action="{{ route('signup.post') }}">

        @csrf

        <div>
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <button type="submit">確認メールを送信</button>
        </div>
    </form>
</main>
@endsection

@section('script_body')
@endsection
