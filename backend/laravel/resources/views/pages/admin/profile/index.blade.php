@extends('layouts.default')

@section('title', '管理者プロフィール')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>管理者プロフィール</h1>

    @if (session('status') || session('success'))
        <p>{{ session('status') ?: session('success') }}</p>
    @endif

    <h2>管理者情報</h2>
    <table>
        <tbody>
            <tr>
                <th>名前</th>
                <td>{{ $admin->name }}</td>
            </tr>

            @if ($isOwn)
                <tr>
                    <th>メールアドレス</th>
                    <td>{{ $admin->email }}</td>
                </tr>
                <tr>
                    <th>登録日</th>
                    <td>{{ $admin->created_at->format('Y年m月d日') }}（{{ $admin->created_at->isoFormat('ddd') }}）{{ $admin->created_at->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>最終更新日</th>
                    <td>{{ $admin->updated_at->format('Y年m月d日') }}（{{ $admin->updated_at->isoFormat('ddd') }}）{{ $admin->updated_at->format('H:i') }}</td>
                </tr>
            @endif

        </tbody>
    </table>

    @if ($isOwn)
        <a href="{{ route('admin.profile.edit', ['id' => $admin->id]) }}">
            管理者プロフィール編集
        </a>
    @endif

    <a href="{{ route('admin.dashboard') }}" class="btn">ダッシュボードに戻る</a>
</main>
@endsection

@section('script_body')
@endsection
