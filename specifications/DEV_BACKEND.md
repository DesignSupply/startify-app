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

マイグレーションファイルを作成、マイグレーションを実行しテーブルを作成します。

- マイグレーション
  - ファイル名: `backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_admin_users_table.php`
  - クラス名: `CreateAdminUsersTable`
  - テーブル名: `admin_users`
  - カラム
    - `id` / integer / primary key / auto increment
    - `name` / string
    - `email` / string / unique
    - `email_verified_at` / timestamp / nullable
    - `password` / string
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable

### 5.2 AdminUserモデルの作成

管理者ユーザー用のモデルファイルを作成します。

- モデル
  - モデル名: `AdminUser`
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

### 5.7 ユーザー用シーダーの作成と実行、テスト

シーダーを実行しユーザーデータを作成、ログインテストを行います。

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

## 6. パスワードリセット（一般ユーザー、管理者ユーザー）機能の実装

一般ユーザーと管理者ユーザーそれぞれにパスワードリセット機能を実装します。

### 6.1 管理者ユーザーのパスワードリセットトークン用テーブルのマイグレーションファイルを作成

パスワードリセットトークン用のテーブルを作成します。マイグレーションファイルを作成、マイグレーションを実行し `admin_password_reset_tokens` テーブルを作成します。

なお、一般ユーザー用のパスワードリセットトークン用テーブルは既存の `password_reset_tokens` テーブルを使用します。

- マイグレーション（管理者用）
  - ファイル名: `backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_admin_password_reset_tokens_table.php`
  - クラス名: `CreateAdminPasswordResetTokensTable`
  - テーブル名: `admin_password_reset_tokens`
  - カラム
    - `email` / string / primary key
    - `token` / string
    - `created_at` / timestamp

### 6.2 パスワードリセット（一般ユーザー、管理者ユーザー）用のモデル作成

一般ユーザーと管理者ユーザーのパスワードリセットトークン用のモデルファイルを作成します。

- モデル（一般ユーザー）
  - モデル名: `PasswordResetToken`
  - ファイル名: `backend/laravel/app/Models/PasswordResetToken.php`
  - クラス名: `PasswordResetToken`
- モデル（管理者ユーザー）
  - モデル名: `AdminPasswordResetToken`
  - ファイル名: `backend/laravel/app/Models/AdminPasswordResetToken.php`
  - クラス名: `AdminPasswordResetToken`

### 6.3 パスワードリセット（一般ユーザー、管理者ユーザー）用の通知クラスの作成

一般ユーザーと管理者ユーザーのパスワードリセット用の通知クラスを作成します。トークンの生成やメール送信処理を作成します。

- 通知クラス（一般ユーザー）
  - クラス名: `PasswordResetNotification`
  - ファイル名: `backend/laravel/app/Notifications/PasswordResetNotification.php`
- 通知クラス（管理者ユーザー）
  - クラス名: `AdminPasswordResetNotification`
  - ファイル名: `backend/laravel/app/Notifications/AdminPasswordResetNotification.php`

### 6.4 パスワードリセット（一般ユーザー、管理者ユーザー）用のメールテンプレートの作成

パスワードリセット用の通知メールテンプレートを作成します。

- メールテンプレート（一般ユーザー）
  - ファイル名: `backend/laravel/resources/views/emails/password-reset.blade.php`
  - メール件名：パスワードリセットのお知らせ
- メールテンプレート（管理者ユーザー）
  - ファイル名: `backend/laravel/resources/views/emails/admin-password-reset.blade.php`
  - メール件名：管理者パスワードリセットのお知らせ

### 6.5 パスワードリセット（一般ユーザー、管理者ユーザー）用のビューの作成

パスワードリセット用のビューを作成します。メールアドレス確認用フォーム画面と、パスワード再設定用フォーム画面を作成します。

- ビュー（一般ユーザー）
  - ファイル名（メールアドレス確認）: `backend/laravel/resources/views/static/password-forgot/index.blade.php`
  - ファイル名（パスワード再設定）: `backend/laravel/resources/views/static/password-reset/index.blade.php`
- ビュー（管理者ユーザー）
  - ファイル名（メールアドレス確認）: `backend/laravel/resources/views/static/admin/password-forgot/index.blade.php`
  - ファイル名（パスワード再設定）: `backend/laravel/resources/views/static/admin/password-reset/index.blade.php`
- 機能仕様
  - メールアドレス確認用フォーム画面ではメールアドレスと入力するフォームを表示します
  - パスワード再設定用フォーム画面ではパスワードと入力するフォームを入力用と確認用の2つを表示し、同じ値であることを確認します
  - ログインページにはメールアドレス確認用フォーム画面のリンクを表示します

### 6.6 パスワードリセット（一般ユーザー、管理者ユーザー）用のルーティングの作成

パスワードリセット用のルーティングを作成します。

- ルーティング（一般ユーザー）
  - メールアドレス確認（画面表示）
    - パス: `/password-forgot`
    - メソッド: `GET`
    - ルーティング名: `password-forgot`
  - メールアドレス確認（フォーム送信）
    - パス: `/password-forgot`
    - メソッド: `POST`
    - ルーティング名: `password-forgot.post`
  - パスワード再設定（画面表示）
    - パス: `/password-reset`
    - メソッド: `GET`
    - ルーティング名: `password-reset`
  - パスワード再設定（フォーム送信）
    - パス: `/password-reset`
    - メソッド: `POST`
    - ルーティング名: `password-reset.post`
- ルーティング（管理者ユーザー）
  - メールアドレス確認（画面表示）
    - パス: `/admin/password-forgot`
    - メソッド: `GET`
    - ルーティング名: `admin.password-forgot`
  - メールアドレス確認（フォーム送信）
    - パス: `/admin/password-forgot`
    - メソッド: `POST`
    - ルーティング名: `admin.password-forgot.post`
  - パスワード再設定（画面表示）
    - パス: `/admin/password-reset`
    - メソッド: `GET`
    - ルーティング名: `admin.password-reset`
  - パスワード再設定（フォーム送信）
    - パス: `/admin/password-reset`
    - メソッド: `POST`
    - ルーティング名: `admin.password-reset.post`

### 6.7 パスワードリセット（一般ユーザー、管理者ユーザー）用のコントローラーの作成

パスワードリセット用のコントローラーを作成します。メールの送信、トークンの生成、バリデーション処理も含めて作成します。

- コントローラー（一般ユーザー）
  - メールアドレス確認
    - クラス名: `PasswordForgotController`
    - ファイル名: `backend/laravel/app/Http/Controllers/PasswordForgotController.php`
    - メソッド
      - `index`
      - `sendMail`
  - パスワード再設定
    - クラス名: `PasswordResetController`
    - ファイル名: `backend/laravel/app/Http/Controllers/PasswordResetController.php`
    - メソッド
      - `index`
      - `reset`
- コントローラー（管理者ユーザー）
  - メールアドレス確認
    - クラス名: `AdminPasswordForgotController`
    - ファイル名: `backend/laravel/app/Http/Controllers/AdminPasswordForgotController.php`
    - メソッド
      - `index`
      - `sendMail`
  - パスワード再設定
    - クラス名: `AdminPasswordResetController`
    - ファイル名: `backend/laravel/app/Http/Controllers/AdminPasswordResetController.php`
    - メソッド
      - `index`
      - `reset`

メール送信やトークン生成に伴い下記Laravelの設定ファイルも必要に応じて修正します。

- `backend/laravel/config/mail.php`
- `backend/laravel/config/auth.php`
