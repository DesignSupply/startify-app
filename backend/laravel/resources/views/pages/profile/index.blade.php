@extends('layouts.default')

@section('title', 'プロフィール')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>プロフィール</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>ユーザー情報</h2>
    <table>
        <tbody>
            <tr>
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>
            @if ($isOwn)
                <tr>
                    <th>メールアドレス</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th>登録日</th>
                    <td>{{ $user->created_at->format('Y年m月d日') }}（{{ $user->created_at->isoFormat('ddd') }}）{{ $user->created_at->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>最終更新日</th>
                    <td>{{ $user->updated_at->format('Y年m月d日') }}（{{ $user->updated_at->isoFormat('ddd') }}）{{ $user->updated_at->format('H:i') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <a href="{{ route('home') }}" class="btn">ホームに戻る</a>
</main>
@endsection

@section('script_body')
@endsection
