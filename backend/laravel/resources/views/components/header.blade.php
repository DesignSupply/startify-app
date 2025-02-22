<header class="app-header">
    <nav>
        @if (
            !str_contains(Route::currentRouteName(), 'password-reset')
            && !str_contains(Route::currentRouteName(), 'admin.password-reset')
        )
            <a href="{{ route('frontpage') }}">フロントページ</a>
        @endif
        @if (
            !str_contains(Route::currentRouteName(), 'password-reset')
            && Route::currentRouteName() !== 'signin'
            && !str_contains(Route::currentRouteName(), 'admin')
        )
            @if (Auth::check())
                <a href="{{ route('home') }}">ホーム</a>
                <form method="POST" action="{{ route('signout.post') }}" style="display: inline;">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            @else
                <a href="{{ route('signin') }}">ログイン</a>
            @endif
        @endif
        @if (
            Route::currentRouteName() === 'frontpage'
            || (str_contains(Route::currentRouteName(), 'admin')
            && Route::currentRouteName() !== 'admin'
            && !str_contains(Route::currentRouteName(), 'admin.password-reset'))
        )
            @if (Auth::guard('admin')->check())
                <a href="{{ route('admin.dashboard') }}">管理者ダッシュボード</a>
                <form method="POST" action="{{ route('admin.signout.post') }}" style="display: inline;">
                    @csrf
                    <button type="submit">管理者ログアウト</button>
                </form>
            @else
                <a href="{{ route('admin') }}">管理者ログイン</a>
            @endif
        @endif
    </nav>
</header>
