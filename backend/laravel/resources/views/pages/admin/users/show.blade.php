@extends('layouts.default')

@section('title', 'ユーザー詳細')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ユーザー詳細</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <table>
        <tbody>
            <tr>
                <th>ID</th>
                <td>{{ $user->id }}</td>
            </tr>
            <tr>
                <th>ユーザー名</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>メールアドレス</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th>作成日時</th>
                <td>{{ $user->created_at->format('Y年m月d日') }}（{{ $user->created_at->isoFormat('ddd') }}）{{ $user->created_at->format('H:i') }}</td>
            </tr>
            <tr>
                <th>削除状態</th>
                <td>
                    @if ($user->is_deleted)
                        <span>削除済み</span>
                    @else
                        <span>-</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <div>
        <a href="{{ route('admin.users.edit', ['id' => $user->id]) }}">編集</a>
    </div>

    <a href="{{ route('admin.users.index') }}">ユーザー一覧に戻る</a>
</main>
@endsection

@section('script_body')
@endsection


