@extends('layouts.default')

@section('title', 'ユーザー一覧')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>ユーザー一覧</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @if (isset($users) && $users->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ユーザー名</th>
                    <th>削除状態</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', ['id' => $user->id]) }}">{{ $user->id }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', ['id' => $user->id]) }}">{{ $user->name }}</a>
                        </td>
                        <td>
                            @if ($user->is_deleted)
                                <span>削除済み</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>ユーザーが存在しません。</p>
    @endif

    <a href="{{ route('admin.dashboard') }}">ダッシュボードに戻る</a>
</main>
@endsection

@section('script_body')
@endsection


