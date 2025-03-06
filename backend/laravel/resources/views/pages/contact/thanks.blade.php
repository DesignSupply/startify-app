@extends('layouts.default')

@section('title', 'お問い合わせ完了')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>お問い合わせ完了</h1>
    <p>
        お問い合わせありがとうございます。<br>
        内容を確認の上、担当者より折り返しご連絡させていただきます。<br>
        また、ご入力いただいたメールアドレス宛に確認メールをお送りしましたのでご確認ください。
    </p>
    <a href="{{ route('frontpage') }}">フロントページへ戻る</a>
</main>
@endsection

@section('script_body')
@endsection
