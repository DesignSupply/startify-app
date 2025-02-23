@extends('layouts.default')

@section('title', '新規ユーザー登録完了')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>新規ユーザー登録完了</h1>
    <p>
        ユーザー登録が完了しました。<br>
        下記のリンクからログインしてください。
    </p>
    <div>
        <a href="{{ route('signin') }}">ログイン画面へ</a>
    </div>
</main>
@endsection

@section('script_body')
@endsection
