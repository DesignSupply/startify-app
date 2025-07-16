---
title: バックエンド実装タスクリスト（Laravel）:管理者ログイン機能の実装
id: laravel_task_004
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:管理者ログイン機能の実装

管理者ユーザーとしてメールアドレスとパスワードを用いたログイン機能を実装します。

---

## 1. 管理者ユーザーテーブルのマイグレーションファイルを作成

マイグレーションファイルを作成、マイグレーションを実行しテーブルを作成します。

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_admin_users_table.php`
  - クラス: `CreateAdminUsersTable`
  - テーブル: `admin_users`
  - カラム
    - `id` / big integer / primary key / auto increment / unsigned
    - `name` / string
    - `email` / string / unique
    - `email_verified_at` / timestamp / nullable
    - `password` / string
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable

---

## 2. 管理者ユーザーのモデルを作成

管理者ユーザーのモデルを作成します。

- モデル
  - モデル: `AdminUser`
  - パス: `/backend/laravel/app/Models/AdminUser.php`
  - クラス: `AdminUser`
  
管理者ユーザーのモデル作成後 `config/auth.php` の `guards` と `providers` に `admin_users` の設定を追加します。

---

## 3. 管理者ログイン画面のビューを作成

管理者ログインフォームを表示させるページのビューを作成します。

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/index.blade.php`
- 機能仕様
  - 画面上にはメールアドレスとパスワードを入力するフォームを表示します
  - 画面内にはログインボタンを設置し、ログインできるようにします

---

## 4. 管理者ログイン画面のルーティングを作成

管理者ログインのルーティングを作成します。

- ルーティング（管理者ログイン画面表示）
  - パス: `/admin`
  - メソッド: `GET`
  - ルート名: `admin`
- ルーティング（管理者ログイン処理）
  - パス: `/admin/signin`
  - メソッド: `POST`
  - ルート名: `admin.signin`

---

## 5. 管理者ログイン画面のコントローラーを作成

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

---

## 6. 管理者ログイン後のリダイレクト先画面（管理者ダッシュボード画面）を作成

管理者ログイン成功時にリダイレクトされる画面（管理者ダッシュボード画面）を作成します。

- ルーティング
  - パス: `/admin/dashboard`
  - メソッド: `GET`
  - ルート名: `dashboard`
- コントローラー
  - クラス: `DashboardController`
  - メソッド: `index`
- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/dashboard/index.blade.php`
- 機能仕様
  - 画面上には管理者用のダッシュボード画面を表示します
  - 画面内にはログアウトボタンを設置し、ログアウトできるようにします
  - 未認証の場合には `/admin` にリダイレクトします

---

## 7. 管理者ログアウト処理の実装

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

---

## 8. 管理者ユーザー用シーダーの作成と実行、テスト

シーダーを実行し管理者ユーザーデータを作成、管理者ログインテストを行います。

- シーダー
  - パス: `/backend/laravel/database/seeders/AdminUserSeeder.php`
  - クラス: `AdminUserSeeder`
- テストユーザー
  - 名前: `管理者 太郎`
  - メールアドレス: `admin@example.com`
  - パスワード: `password`
- 機能仕様
  - シーダーは、`/backend/laravel/database/seeders/DatabaseSeeder.php` のメソッドを使用し、一括で実行できるようにします

---
