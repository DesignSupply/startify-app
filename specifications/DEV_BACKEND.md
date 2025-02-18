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

- ルート
  - パス: `/`
  - メソッド: `GET`
  - ルーティング名: `frontpage`
- コントローラー
  - クラス名: `FrontPageController`
  - メソッド: `index`
- ビュー
  - パス: `backend/laravel/resources/views/static/frontpage/index.blade.php`

---

## 4. ログイン機能の実装

メールアドレスとパスワードを用いたログイン機能を実装します。

### 4.1. ログイン画面の作成

ログインフォームを表示させるページを作成します。

- ルート
  - パス: `/signin`
  - メソッド: `GET`
  - ルーティング名: `signin`
- コントローラー
  - クラス名: `SigninController`
  - メソッド: `index`
- ビュー
  - パス: `backend/laravel/resources/views/static/signin/index.blade.php`
- 機能仕様
  - 画面上にはメールアドレスとパスワードを入力するフォームを表示します
  - 画面内にはログインボタンを設置し、ログインできるようにします

### 4.2. ログイン処理の実装

ログイン認証に必要な処理やモデルを定義します、Authファサードを使用して、認証に必要な設定を行います。

- ルート
  - パス: `/signin`
  - メソッド: `POST`
  - ルーティング名: `signin.post`
- コントローラー
  - クラス名: `SigninController`
  - メソッド: `signIn`
- 機能仕様
  - ログイン成功時には `/home` にリダイレクトします
  - ログイン成功時にはセッションを作成し、ログイン情報をセッションに保存します
  - ログイン失敗時には `/signin` にリダイレクトし、エラーメッセージを表示します

### 4.3. ログイン後の画面の作成

ログイン成功時にリダイレクトされる画面を作成します。

- ルート
  - パス: `/home`
  - メソッド: `GET`
  - ルーティング名: `home`
- コントローラー
  - クラス名: `HomeController`
  - メソッド: `index`
- ビュー
  - パス: `backend/laravel/resources/views/auth/home/index.blade.php`
- 機能仕様
  - 画面上にはログイン成功後のメッセージを表示します
  - 画面内にはログアウトボタンを設置し、ログアウトできるようにします

### 4.4. ログアウト処理の実装

ログアウト処理を実装します。

- ルート
  - パス: `/signout`
  - メソッド: `POST`
  - ルーティング名: `signout.post`
- コントローラー
  - クラス名: `SignoutController`
  - メソッド: `signOut`
- 機能仕様
  - ログアウトボタンをクリックすると、セッションを破棄します
  - ログアウト後には `/signin` にリダイレクトします

### 4.5. シーダーを実行しテストとユーザーを作成、ログインテスト

シーダーを実行しテストとユーザーを作成、ログインテストを行います。

- シーダー
  - パス: `backend/laravel/database/seeders/UserSeeder.php`
  - クラス名: `UserSeeder`
- テストユーザー
  - 名前: `テスト 太郎`
  - メールアドレス: `test@example.com`
  - パスワード: `password`
- 機能仕様
  - シーダーは、`backend/laravel/database/seeders/DatabaseSeeder.php` のメソッドを使用し、一括で実行できるようにします

---

## 5. 管理者ログイン機能の実装

管理者ユーザーとしてメールアドレスとパスワードを用いたログイン機能を実装します。

### 5.1 admin_usersテーブルのマイグレーションファイルを作成

マイグレーションファイルを作成、マイグレーションを実行し、`admin_users` テーブルを作成します。

- マイグレーション
  - ファイル名: `backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_admin_users_table.php`
  - クラス名: `CreateAdminUsersTable`
  - カラム
    - `id` / integer / primary key / auto increment
    - `name` / string
    - `email` / string / unique
    - `email_verified_at` / timestamp / nullable
    - `password` / string
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable

### 5.2 AdminUserモデルの作成

`AdminUser` モデルを作成します。

- モデル
  - ファイル名: `backend/laravel/app/Models/AdminUser.php`
  - クラス名: `AdminUser`
  
`config/auth.php` の `guards` と `providers` に `admin_users` の設定を追加します。

### 5.3 ログイン画面の作成

管理者ログインフォームを表示させるページを作成します。

- ルート
  - パス: `/admin`
  - メソッド: `GET`
  - ルーティング名: `admin`
- コントローラー
  - クラス名: `AdminController`
  - メソッド: `index`
- ビュー
  - パス: `backend/laravel/resources/views/static/admin/index.blade.php`
- 機能仕様
  - 画面上にはメールアドレスとパスワードを入力するフォームを表示します
  - 画面内にはログインボタンを設置し、ログインできるようにします

### 5.4 ログイン処理の実装

ログイン認証に必要な処理やモデルを定義します、Authファサードを使用して、認証に必要な設定を行います。

- ルート
  - パス: `/admin`
  - メソッド: `POST`
  - ルーティング名: `admin.signin.post`
- コントローラー
  - クラス名: `AdminController`
  - メソッド: `signIn`
- 機能仕様
  - ログイン成功時には `/admin/dashboard` にリダイレクトします
  - ログイン成功時にはセッションを作成し、ログイン情報をセッションに保存します
  - ログイン失敗時には `/admin` にリダイレクトし、エラーメッセージを表示します

### 5.5 管理者用ダッシュボード画面の作成

管理者ログイン成功時にリダイレクトされる画面を作成します。

- ルート
  - パス: `/admin/dashboard`
  - メソッド: `GET`
  - ルーティング名: `dashboard`
- コントローラー
  - クラス名: `DashboardController`
  - メソッド: `index`
- ビュー
  - パス: `backend/laravel/resources/views/auth/admin/dashboard/index.blade.php`
- 機能仕様
  - 画面上には管理者用のダッシュボード画面を表示します
  - 画面内にはログアウトボタンを設置し、ログアウトできるようにします

### 5.6 ログアウト処理の実装

ログアウト処理を実装します。

- ルート
  - パス: `/admin/signout`
  - メソッド: `POST`
  - ルーティング名: `admin.signout.post`
- コントローラー
  - クラス名: `AdminController`
  - メソッド: `signOut`
- 機能仕様
  - ログアウトボタンをクリックすると、セッションを破棄します
  - ログアウト後には `/admin` にリダイレクトします

### 5.7 シーダーを実行しテストとユーザーを作成、ログインテスト

シーダーを実行しテストとユーザーを作成、ログインテストを行います。

- シーダー
  - パス: `backend/laravel/database/seeders/AdminUserSeeder.php`
  - クラス名: `AdminUserSeeder`
- テストユーザー
  - 名前: `管理者 太郎`
  - メールアドレス: `admin@example.com`
  - パスワード: `password`
- 機能仕様
  - シーダーは、`backend/laravel/database/seeders/DatabaseSeeder.php` のメソッドを使用し、一括で実行できるようにします
  
---

