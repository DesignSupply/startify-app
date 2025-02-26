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

    <div>
        <p>ようこそ、{{ $admin->name }}さん</p>
    </div>

    <div>
        <form method="POST" action="{{ route('admin.signout') }}">
            @csrf
            <button type="submit">管理者ログアウト</button>
        </form>
    </div>
</main>
@endsection

@section('script_body')
@endsection
