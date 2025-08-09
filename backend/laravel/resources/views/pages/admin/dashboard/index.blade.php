@extends('layouts.default')

@section('title', '管理者ダッシュボード')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>管理者ダッシュボード</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <p>こんにちは、{{ $admin->name }}さん</p>
    <a href="{{ route('admin.profile', $admin->id) }}">管理者プロフィール</a>
    <br>
    <a href="{{ route('admin.files.index') }}">ファイル一覧</a>
    <br>
    <a href="{{ route('admin.users.index') }}">一般ユーザー一覧</a>
    <br>
    <form method="POST" action="{{ route('admin.signout') }}">
        @csrf
        <button type="submit">管理者ログアウト</button>
    </form>
</main>
@endsection

@section('script_body')
@endsection
