# バックエンド実装（Laravel）

## 0. 機能実装におけるコーディングルール

### 0.1 実装手順

各種機能の実装において下記の順番で進めていくことを基本とします。実装する上で不要な手順は省略します。

1. マイグレーションファイルの作成
2. モデルの作成
3. ビューの作成
4. ルーティングの作成
5. コントローラー、その他処理クラスの作成
6. シーダーの作成
7. テストの作成・実施

### 0.2 命名規則

- マイグレーションのファイルパスは `backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_[操作種別]_[テーブル名]_table.php` とします
- モデルのファイルパスは `backend/laravel/app/Models/[モデル名].php` とします
- コントローラーのファイルパスは `backend/laravel/app/Http/Controllers/[コントローラー名].php` とします
- シーダーのファイルパスは `backend/laravel/database/seeders/[シーダー名].php` とします
- ビューのファイルパスは `backend/laravel/resources/views/[ディレクトリ名]/[ファイル名].blade.php` とし、ディレクトリ名は `components` と `layouts` と `pages` と `emails` の4つを用意し、用途に応じて格納するディレクトリを分けるようにします
- モデルはアッパーケースで記載します
- ビューファイルはケバブケースで記載します
- ルーティングのパスはケバブケースで記載します
- コントローラーはアッパーケースで記載します
- シーダーはアッパーケースで記載します
- マイグレーションファイルはスネークケースで記載します
- クラスはアッパーケースで記載します
- メソッドはキャメルケースで記載します
- ルーティングのHTTPメソッドは、`GET` と `POST` に限定し、データ操作をできるようにルーティングやパスの命名規則で区別できるようにします

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

## 3. フロントページの実装

アプリケーションのトップ画面となるフロントページを表示させる機能を実装します。

### 3.1 フロントページのビューを作成

フロントページのビューを作成します。

- ビュー
  - パス: `backend/laravel/resources/views/pages/frontpage/index.blade.php`
- 機能仕様
  - 一般ユーザーのログイン画面、管理者ユーザーのログイン画面へのリンクを設置します
  
### 3.2 フロントページのルーティングを作成

フロントページのルーティングを作成します。

- ルーティング
  - パス: `/`
  - メソッド: `GET`
  - ルート名: `frontpage`
  
### 3.3 フロントページのコントローラーを作成

フロントページのコントローラーを作成します。

- コントローラー
  - クラス: `FrontPageController`
  - メソッド: `index`

---

## 4. 一般ユーザーログイン機能の実装

一般ユーザー用のメールアドレスとパスワードを用いたログイン機能を実装します。

### 4.1. ログイン画面のビューを作成

一般ユーザーログインフォームを表示させるページのビューを作成します。

- ビュー
  - パス: `backend/laravel/resources/views/pages/signin/index.blade.php`
- 機能仕様
  - 画面上にはメールアドレスとパスワードを入力するフォームを表示します
  - 画面内にはログインボタンを設置し、ログインできるようにします
  - 画面内にはパスワードリセット用のリンクを設置します
  
### 4.2. ログイン画面のルーティングを作成

一般ユーザーログインのルーティングを作成します。

- ルーティング（ログイン画面表示）
  - パス: `/signin`
  - メソッド: `GET`
  - ルート名: `signin`
- ルーティング（ログイン処理）
  - パス: `/signin/auth`
  - メソッド: `POST`
  - ルート名: `signin.auth`

### 4.3. ログイン画面のコントローラーを作成

ログイン画面のコントローラーを作成します。Authファサードを使用して、ログイン認証に必要な設定を行います。必要に応じてバリデーション処理も実装します。

- コントローラー（ログイン画面表示）
  - クラス: `SigninController`
  - メソッド: `index`
- コントローラー（ログイン処理）
  - クラス: `SigninController`
  - メソッド: `signIn`
- 機能仕様
  - ログイン成功時には `/home` にリダイレクトします
  - ログイン成功時にはセッションを作成し、ログイン情報をセッションに保存します
  - ログイン失敗時には `/signin` にリダイレクトし、エラーメッセージを表示します

### 4.3. ログイン後のリダイレクト先画面を作成

ログイン成功時にリダイレクトされる画面を作成します。

- ルーティング
  - パス: `/home`
  - メソッド: `GET`
  - ルート名: `home`
- コントローラー
  - クラス: `HomeController`
  - メソッド: `index`
- ビュー
  - パス: `backend/laravel/resources/views/pages/home/index.blade.php`
- 機能仕様
  - 画面上にはログイン成功後のメッセージを表示します
  - 画面内にはログアウトボタンを設置し、ログアウトできるようにします
  - 未認証の場合には `/signin` にリダイレクトします

### 4.4. ログアウト処理の実装

ログアウト処理を実装します。

- ルーティング
  - パス: `/signout`
  - メソッド: `POST`
  - ルート名: `signout`
- コントローラー
  - クラス: `SignoutController`
  - メソッド: `signOut`
- 機能仕様
  - ログアウトボタンをクリックすると、セッションを破棄します
  - ログアウト後には `/signin` にリダイレクトします

### 4.5. シーダーを実行しテストとユーザーを作成、ログインテスト

シーダーを実行しテストとユーザーを作成、ログインテストを行います。

- シーダー
  - パス: `backend/laravel/database/seeders/UserSeeder.php`
  - クラス: `UserSeeder`
- テストユーザー
  - 名前: `テスト 太郎`
  - メールアドレス: `test@example.com`
  - パスワード: `password`
- 機能仕様
  - シーダーは、`backend/laravel/database/seeders/DatabaseSeeder.php` のメソッドを使用し、一括で実行できるようにします

---

## 5. 管理者ログイン機能の実装

管理者ユーザーとしてメールアドレスとパスワードを用いたログイン機能を実装します。

### 5.1 管理者ユーザーテーブルのマイグレーションファイルを作成

マイグレーションファイルを作成、マイグレーションを実行しテーブルを作成します。

- マイグレーション
  - パス: `backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_admin_users_table.php`
  - クラス: `CreateAdminUsersTable`
  - テーブル: `admin_users`
  - カラム
    - `id` / integer / primary key / auto increment
    - `name` / string
    - `email` / string / unique
    - `email_verified_at` / timestamp / nullable
    - `password` / string
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable

### 5.2 管理者ユーザーのモデルを作成

管理者ユーザーのモデルを作成します。

- モデル
  - モデル: `AdminUser`
  - パス: `backend/laravel/app/Models/AdminUser.php`
  - クラス: `AdminUser`
  
管理者ユーザーのモデル作成後 `config/auth.php` の `guards` と `providers` に `admin_users` の設定を追加します。

### 5.3 管理者ログイン画面のビューを作成

管理者ログインフォームを表示させるページのビューを作成します。

- ビュー
  - パス: `backend/laravel/resources/views/pages/admin/index.blade.php`
- 機能仕様
  - 画面上にはメールアドレスとパスワードを入力するフォームを表示します
  - 画面内にはログインボタンを設置し、ログインできるようにします

### 5.4 管理者ログイン画面のルーティングを作成

管理者ログインのルーティングを作成します。

- ルーティング（管理者ログイン画面表示）
  - パス: `/admin`
  - メソッド: `GET`
  - ルート名: `admin`
- ルーティング（管理者ログイン処理）
  - パス: `/admin/signin`
  - メソッド: `POST`
  - ルート名: `admin.signin`

### 5.5 管理者ログイン画面のコントローラーを作成

管理者ログインのコントローラーを作成します。Authファサードを使用して、認証に必要な設定を行います。必要に応じてバリデーション処理も実装します。

- コントローラー（管理者ログイン画面表示）
  - クラス: `AdminController`
  - メソッド: `index`
- コントローラー（管理者ログイン処理）
  - クラス: `AdminController`
  - メソッド: `signIn`
- 機能仕様
  - ログイン成功時には `/admin/dashboard` にリダイレクトします
  - ログイン成功時にはセッションを作成し、ログイン情報をセッションに保存します
  - ログイン失敗時には `/admin` にリダイレクトし、エラーメッセージを表示します

### 5.5 管理者ログイン後のリダイレクト先画面（管理者ダッシュボード画面）を作成

管理者ログイン成功時にリダイレクトされる画面（管理者ダッシュボード画面）を作成します。

- ルーティング
  - パス: `/admin/dashboard`
  - メソッド: `GET`
  - ルート名: `dashboard`
- コントローラー
  - クラス: `DashboardController`
  - メソッド: `index`
- ビュー
  - パス: `backend/laravel/resources/views/pages/admin/dashboard/index.blade.php`
- 機能仕様
  - 画面上には管理者用のダッシュボード画面を表示します
  - 画面内にはログアウトボタンを設置し、ログアウトできるようにします
  - 未認証の場合には `/admin` にリダイレクトします

### 5.6 管理者ログアウト処理の実装

管理者ログアウトの処理を実装します。

- ルーティング
  - パス: `/admin/signout`
  - メソッド: `POST`
  - ルート名: `admin.signout`
- コントローラー
  - クラス: `AdminController`
  - メソッド: `signOut`
- 機能仕様
  - ログアウトボタンをクリックすると、セッションを破棄します
  - ログアウト後には `/admin` にリダイレクトします

### 5.7 管理者ユーザー用シーダーの作成と実行、テスト

シーダーを実行し管理者ユーザーデータを作成、管理者ログインテストを行います。

- シーダー
  - パス: `backend/laravel/database/seeders/AdminUserSeeder.php`
  - クラス: `AdminUserSeeder`
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
  - パス: `backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_admin_password_reset_tokens_table.php`
  - クラス: `CreateAdminPasswordResetTokensTable`
  - テーブル: `admin_password_reset_tokens`
  - カラム
    - `email` / string / primary key
    - `token` / string
    - `created_at` / timestamp

### 6.2 パスワードリセット（一般ユーザー、管理者ユーザー）用のモデルを作成

一般ユーザーと管理者ユーザーのパスワードリセットトークン用のモデルファイルを作成します。

- モデル（一般ユーザー）
  - モデル: `PasswordResetToken`
  - パス: `backend/laravel/app/Models/PasswordResetToken.php`
  - クラス: `PasswordResetToken`
- モデル（管理者ユーザー）
  - モデル: `AdminPasswordResetToken`
  - パス: `backend/laravel/app/Models/AdminPasswordResetToken.php`
  - クラス: `AdminPasswordResetToken`

### 6.3 パスワードリセット（一般ユーザー、管理者ユーザー）用のビューを作成

パスワードリセット用のビューを作成します。メールアドレス確認用フォーム画面と、パスワード再設定用フォーム画面を作成します。

- ビュー（一般ユーザー）
  - パス（メールアドレス確認）: `backend/laravel/resources/views/pages/password-forgot/index.blade.php`
  - パス（パスワード再設定）: `backend/laravel/resources/views/pages/password-reset/index.blade.php`
- ビュー（管理者ユーザー）
  - パス（メールアドレス確認）: `backend/laravel/resources/views/pages/admin/password-forgot/index.blade.php`
  - パス（パスワード再設定）: `backend/laravel/resources/views/pages/admin/password-reset/index.blade.php`
- 機能仕様
  - メールアドレス確認用フォーム画面ではメールアドレスと入力するフォームを表示します
  - パスワード再設定用フォーム画面ではパスワードと入力するフォームを入力用と確認用の2つを表示し、同じ値であることを確認します
  - ログインページにはメールアドレス確認用フォーム画面のリンクを表示します

### 6.4 パスワードリセット（一般ユーザー、管理者ユーザー）用のメールテンプレートを作成

パスワードリセット用の通知メールテンプレートを作成します。

- メールテンプレート（一般ユーザー）
  - パス: `backend/laravel/resources/views/emails/password-reset.blade.php`
  - メール件名：パスワードリセットのお知らせ
- メールテンプレート（管理者ユーザー）
  - パス: `backend/laravel/resources/views/emails/admin-password-reset.blade.php`
  - メール件名：管理者パスワードリセットのお知らせ

### 6.5 パスワードリセット（一般ユーザー、管理者ユーザー）用のルーティングを作成

パスワードリセット用のルーティングを作成します。

- ルーティング（一般ユーザー）
  - メールアドレス確認（画面表示）
    - パス: `/password-forgot`
    - メソッド: `GET`
    - ルート名: `password-forgot`
  - メールアドレス確認（フォーム送信）
    - パス: `/password-forgot/request`
    - メソッド: `POST`
    - ルート名: `password-forgot.request`
  - パスワード再設定（画面表示）
    - パス: `/password-reset/{token}`
    - メソッド: `GET`
    - ルート名: `password-reset`
  - パスワード再設定（フォーム送信）
    - パス: `/password-reset/reset`
    - メソッド: `POST`
    - ルート名: `password-reset.reset`
- ルーティング（管理者ユーザー）
  - メールアドレス確認（画面表示）
    - パス: `/admin/password-forgot`
    - メソッド: `GET`
    - ルート名: `admin.password-forgot`
  - メールアドレス確認（フォーム送信）
    - パス: `/admin/password-forgot/request`
    - メソッド: `POST`
    - ルート名: `admin.password-forgot.request`
  - パスワード再設定（画面表示）
    - パス: `/admin/password-reset/{token}`
    - メソッド: `GET`
    - ルート名: `admin.password-reset`
  - パスワード再設定（フォーム送信）
    - パス: `/admin/password-reset/reset`
    - メソッド: `POST`
    - ルート名: `admin.password-reset.reset`

### 6.6 パスワードリセット（一般ユーザー、管理者ユーザー）用の通知クラスを作成

一般ユーザーと管理者ユーザーのパスワードリセット用の通知クラスを作成します。

- 通知クラス（一般ユーザー）
  - クラス: `PasswordResetNotification`
  - パス: `backend/laravel/app/Notifications/PasswordResetNotification.php`
- 通知クラス（管理者ユーザー）
  - クラス: `AdminPasswordResetNotification`
  - パス: `backend/laravel/app/Notifications/AdminPasswordResetNotification.php`

### 6.7 パスワードリセット（一般ユーザー、管理者ユーザー）用のコントローラーを作成

パスワードリセット用のコントローラーを作成します。メールの送信、トークンの生成、バリデーション処理も含めて作成します。

- コントローラー（一般ユーザー）
  - メールアドレス確認
    - クラス: `PasswordForgotController`
    - パス: `backend/laravel/app/Http/Controllers/PasswordForgotController.php`
    - メソッド
      - `index`
      - `sendMail`
  - パスワード再設定
    - クラス: `PasswordResetController`
    - パス: `backend/laravel/app/Http/Controllers/PasswordResetController.php`
    - メソッド
      - `index`
      - `reset`
- コントローラー（管理者ユーザー）
  - メールアドレス確認
    - クラス: `AdminPasswordForgotController`
    - パス: `backend/laravel/app/Http/Controllers/AdminPasswordForgotController.php`
    - メソッド
      - `index`
      - `sendMail`
  - パスワード再設定
    - クラス: `AdminPasswordResetController`
    - パス: `backend/laravel/app/Http/Controllers/AdminPasswordResetController.php`
    - メソッド
      - `index`
      - `reset`

メール送信やトークン生成に伴い下記Laravelの設定ファイルも必要に応じて修正します。

- `backend/laravel/config/mail.php`
- `backend/laravel/config/auth.php`

### 6.8 パスワードリセット（一般ユーザー、管理者ユーザー）用のテスト

一般ユーザーと管理者ユーザーを使ってパスワードリセットのテストを行います。

---

## 7. 新規ユーザー登録機能の実装

ログイン認証ができる一般ユーザーを新規登録できる機能を実装します。

### 7.1 新規ユーザー登録画面のビューを作成

新規ユーザー登録画面のビューを作成します。

- ビュー
  - パス（メールアドレス確認）: `backend/laravel/resources/views/pages/signup/verify.blade.php`
  - パス（メール送信完了・確認待ち）: `backend/laravel/resources/views/pages/signup/pending.blade.php`
  - パス（新規登録フォーム）: `backend/laravel/resources/views/pages/signup/register.blade.php`
  - パス（登録完了）: `backend/laravel/resources/views/pages/signup/complete.blade.php`

### 7.2 新規ユーザー登録のメールテンプレートを作成

新規ユーザー登録のメールテンプレートを作成します。

- メールテンプレート
  - パス: `backend/laravel/resources/views/emails/signup-verify.blade.php`
  - メール件名：新規ユーザー登録のお知らせ

### 7.3 新規ユーザー登録のルーティングを作成

新規ユーザー登録のルーティングを作成します。

- ルーティング
  - メールアドレス確認（画面表示）
    - パス: `/signup`
    - メソッド: `GET`
    - ルート名: `signup`
  - メールアドレス確認（フォーム送信）
    - パス: `/signup/request`
    - メソッド: `POST`
    - ルート名: `signup.request`
  - メール送信完了・確認待ち（画面表示）
    - パス: `/signup/pending`
    - メソッド: `GET`
    - ルート名: `signup.pending`
  - メールアドレス検証（トークン検証）
    - パス: `/signup/verify/{token}`
    - メソッド: `GET`
    - ルート名: `signup.verify`
  - 新規登録フォーム（画面表示）
    - パス: `/signup/register`
    - メソッド: `GET`
    - ルート名: `signup.register`
  - 新規登録フォーム（フォーム送信）
    - パス: `/signup/register/store`
    - メソッド: `POST`
    - ルート名: `signup.register.store`
  - 登録完了（画面表示）
    - パス: `/signup/complete`
    - メソッド: `GET`
    - ルート名: `signup.complete`

### 7.4 新規ユーザー登録の通知クラスを作成

新規ユーザー登録の通知クラスを作成します。

- 通知クラス
  - クラス: `SignUpNotification`
  - パス: `backend/laravel/app/Notifications/SignUpNotification.php`

### 7.5 新規ユーザー登録のコントローラーを作成

新規ユーザー登録のコントローラーを作成します。メールの送信、トークンの生成、バリデーション処理も含めて作成します。

- コントローラー
  - クラス: `SignUpController`
  - パス: `backend/laravel/app/Http/Controllers/SignUpController.php`
  - メソッド
    - `index`
    - `verifyEmail`
    - `pending`
    - `verifyToken`
    - `form`
    - `register`
    - `complete`

メール送信やトークン生成に伴い下記Laravelの設定ファイルも必要に応じて修正します。

- `backend/laravel/config/mail.php`
- `backend/laravel/config/auth.php`

### 7.6 新規ユーザー登録のテスト

新規ユーザー登録のテストを行います。

---
