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
    <a href="{{ route('signin') }}">ログイン</a>
</main>
@endsection

@section('script_body')
@endsection
