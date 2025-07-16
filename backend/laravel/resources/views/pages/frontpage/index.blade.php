@extends('layouts.default')

@section('title', 'フロントページ')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>フロントページ</h1>
    <p>アプリケーションのフロントページです。</p>
    @if (Auth::check())
        <form method="POST" action="{{ route('signout') }}" style="display: inline;">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    @else
        <a href="{{ route('signin') }}">ログイン</a>
    @endif
    @if (Auth::guard('admin')->check())
        <form method="POST" action="{{ route('admin.signout') }}" style="display: inline;">
            @csrf
            <button type="submit">管理者ログアウト</button>
        </form>
    @else
        <a href="{{ route('admin') }}">管理者ログイン</a>
    @endif
    <a href="{{ route('signup') }}">新規ユーザー登録</a>
</main>
@endsection

@section('script_body')
@endsection
