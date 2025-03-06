@extends('layouts.default')

@section('title', '管理者プロフィール編集')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>管理者プロフィール編集</h1>

    @foreach(['name', 'email', 'password', 'password_confirmation'] as $field)
        @error($field)
            <p style="color: red;">{{ $message }}</p>
        @enderror
    @endforeach

    <form method="POST" action="{{ route('admin.profile.update', ['id' => $admin->id]) }}">

        @csrf

        <div>
            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}">
        </div>
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}">
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
            <a href="{{ route('admin.profile', ['id' => $admin->id]) }}">戻る</a>
        </div>
    </form>
</main>
@endsection

@section('script_body')
@endsection
