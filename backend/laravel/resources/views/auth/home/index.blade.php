@extends('layouts.default')

@section('title', 'ホーム')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ホーム</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @auth
        <p>こんにちは、{{ Auth::user()->name }} さん</p>
    @endauth

    <form method="POST" action="{{ route('signout') }}">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</main>
@endsection
