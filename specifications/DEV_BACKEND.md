# バックエンド実装（Laravel）

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

- パスは `/` とし、`GET` メソッドでアクセスします
- ルーティング名は `frontpage` とします
- コントローラー名は `FrontPageController` とし、`showPage` アクションでビューを表示させます
- ビューは `backend/laravel/resources/views/static/frontpage/index.blade.php` とします

---

## 4. ログイン機能の実装

メールアドレスとパスワードを用いたログイン機能を実装します

### 4.1. ログイン画面の作成

ログインフォームを表示させるページを作成します

- パスは `/signin` とし、`GET` メソッドでアクセスします
- ルーティング名は `signin` とします
- コントローラー名は `SigninController` とし、`showPage` アクションでビューを表示させます
- ビューは `backend/laravel/resources/views/auth/signin/index.blade.php` とします
- 画面上にはメールアドレスとパスワードを入力するフォームを表示します

### 4.2. ログイン処理の実装

ログイン認証に必要な処理やモデルを定義します

- ログイン認証に必要なモデルは `backend/laravel/app/Models/User.php` とします
- コントローラー名は `SigninController` とし、`signin` アクションでログイン処理を行います
- パスは `/signin` とし、`POST` メソッドでアクセスします
- ルーティング名は `signin.post` とします
- Authファサードを使用して、認証に必要な設定を行います
- ログイン成功時には `/home` にリダイレクトします
- ログイン成功時にはセッションを作成し、ログイン情報をセッションに保存します
- ログイン失敗時には `/signin` にリダイレクトし、エラーメッセージを表示します

### 4.3. ログイン後の画面の作成

ログイン成功時にリダイレクトされる画面を作成します

- パスは `/home` とし、`GET` メソッドでアクセスし、認証を必要とするルートとします
- ルーティング名は `home` とします
- コントローラー名は `HomeController` とし、`showPage` アクションでビューを表示させます
- ビューは `backend/laravel/resources/views/auth/home/index.blade.php` とします
- 画面上にはログイン成功後のメッセージを表示します
- 画面内にはログアウトボタンを設置し、ログアウトできるようにします

### 4.4. ログアウト処理の実装

ログアウト処理を実装します

- パスは `/signout` とし、`POST` メソッドでアクセスします
- ルーティング名は `signout` とします
- コントローラー名は `SignoutController` とし、`signout` アクションでログアウト処理を行います
- ログアウトボタンをクリックすると、セッションを破棄します
- ログアウト後には `/signin` にリダイレクトします

### 4.5. シーダーを実行しテストとユーザーを作成、ログインテスト

シーダーを実行しテストとユーザーを作成、ログインテストを行います

- テストユーザーのシーダーを `backend/laravel/database/seeders/UserSeeder.php` に追加し、`backend/laravel/database/seeders/DatabaseSeeder.php` で実行できるようにします
- テストユーザーはユーザー名とメールアドレス、パスワードの情報を持ちます
- ユーザー名は `テスト 太郎` とし、メールアドレスは `test@example.com` とし、パスワードは `password` とします、パスワードはハッシュ化します
- シーダーを実行し、テストユーザーのメールアドレスとパスワードでログインテストを行います

---
