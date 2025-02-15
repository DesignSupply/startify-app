# バックエンド実装

---

## 1. Viewファイルのコンポーネント対応

全体レイアウト、ヘッダーやフッターなどの共通部分をコンポーネント化します。アプリケーションのデフォルトで用意されているBladeファイルは不要なので削除してから作業を進めます。

### 1.1. コンポーネント作成

下記のコンポーネントをBladeファイルで作成します。

- 基本のレイアウトコンポーネントは `backend/laravel/resources/views/layouts/default.blade.php` とします
- ヘッダーのコンポーネントは `backend/laravel/resources/views/components/header.blade.php` とします
- フッターのコンポーネントは `backend/laravel/resources/views/components/footer.blade.php` とします
- 共通メタタグのコンポーネントは `backend/laravel/resources/views/components/head.blade.php` とします
- オフキャンバス要素のコンポーネントは `backend/laravel/resources/views/components/offcanvas.blade.php` とします

共通パーツはレイアウトコンポーネントで読み込み、ページコンポーネントでは読み込まないようにします。

```html
<!DOCTYPE html>
<html>
    <head>
        @include('components.head')
        @yield('meta')
        @yield('style')
        @yield('script_head')
    </head>
    <body>
        <noscript>※当ウェブサイトを快適に閲覧して頂くためjavascriptを有効にしてください</noscript>
        <div class="app-layout">
            @include('components.header')
            @yield('content')
            @include('components.footer')
        </div>
        @include('components.offcanvas')
        @yield('script_body')
    </body>
</html>
```

ページコンポーネントは下記の形を基本とし、ページ単位でのメタ設定やページ固有のスクリプト、スタイルが使えるようにします。

```php
@extends('layouts.default')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
@endsection

@section('script_body')
@endsection
```

---

## 2. API用ルーティングの追加

API用ルーティングの追加を行い、`backend/laravel/app/bootstrap/app.php` を下記のように変更します。

```bash
cd /server
docker compose exec app bash -c "cd /var/www/html/laravel && php artisan install:api"
```

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

```

設定後はキャッシュをクリアして変更を反映させます。

```bash
cd /server
make laravel-cache-clear
make laravel-config-clear
```

---

## 3. アプリケーションフロントページの作成

アプリケーションのトップ画面となるフロントページを表示させる機能を実装します。

- URLのパスは `/` とし、`GET` メソッドでアクセスします
- ルーティング名は `frontpage` とします
- コントローラー名は `FrontPageController` とし、`showPage` アクションでビューを表示させます
- ビューは `backend/laravel/resources/views/static/frontpage/index.blade.php` とします


