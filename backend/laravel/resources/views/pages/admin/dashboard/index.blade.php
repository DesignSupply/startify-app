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
    <a href="{{ route('admin.profile', $admin->id) }}">プロフィール</a>
    <form method="POST" action="{{ route('admin.signout') }}">
        @csrf
        <button type="submit">管理者ログアウト</button>
    </form>
</main>
@endsection

@section('script_body')
@endsection
