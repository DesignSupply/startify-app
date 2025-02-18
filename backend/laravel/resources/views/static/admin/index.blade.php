@extends('layouts.default')

@section('title', '管理者ログイン')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>管理者ログイン</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p style="color: red;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.signin.post') }}">

        @csrf

        <div>
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div>
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <button type="submit">ログイン</button>
        </div>
    </form>
</main>
@endsection

@section('script_body')
@endsection
