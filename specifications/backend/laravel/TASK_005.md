---
title: バックエンド実装タスクリスト（Laravel）:パスワードリセット（一般ユーザー、管理者ユーザー）機能の実装
id: laravel_task_005
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:パスワードリセット（一般ユーザー、管理者ユーザー）機能の実装

一般ユーザーと管理者ユーザーそれぞれにパスワードリセット機能を実装します。

---

## 1. 管理者ユーザーのパスワードリセットトークン用テーブルのマイグレーションファイルを作成

パスワードリセットトークン用のテーブルを作成します。マイグレーションファイルを作成、マイグレーションを実行し `admin_password_reset_tokens` テーブルを作成します。

なお、一般ユーザー用のパスワードリセットトークン用テーブルは既存の `password_reset_tokens` テーブルを使用します。

- マイグレーション（管理者用）
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_admin_password_reset_tokens_table.php`
  - クラス: `CreateAdminPasswordResetTokensTable`
  - テーブル: `admin_password_reset_tokens`
  - カラム
    - `email` / string / primary key
    - `token` / string
    - `created_at` / timestamp

---

## 2. パスワードリセット（一般ユーザー、管理者ユーザー）用のモデルを作成

一般ユーザーと管理者ユーザーのパスワードリセットトークン用のモデルファイルを作成します。

- モデル（一般ユーザー）
  - モデル: `PasswordResetToken`
  - パス: `/backend/laravel/app/Models/PasswordResetToken.php`
  - クラス: `PasswordResetToken`
- モデル（管理者ユーザー）
  - モデル: `AdminPasswordResetToken`
  - パス: `/backend/laravel/app/Models/AdminPasswordResetToken.php`
  - クラス: `AdminPasswordResetToken`

---

## 3. パスワードリセット（一般ユーザー、管理者ユーザー）用のビューを作成

パスワードリセット用のビューを作成します。メールアドレス確認用フォーム画面と、パスワード再設定用フォーム画面を作成します。

- ビュー（一般ユーザー）
  - パス（メールアドレス確認）: `/backend/laravel/resources/views/pages/password-forgot/index.blade.php`
  - パス（パスワード再設定）: `/backend/laravel/resources/views/pages/password-reset/index.blade.php`
- ビュー（管理者ユーザー）
  - パス（メールアドレス確認）: `/backend/laravel/resources/views/pages/admin/password-forgot/index.blade.php`
  - パス（パスワード再設定）: `/backend/laravel/resources/views/pages/admin/password-reset/index.blade.php`
- 機能仕様
  - メールアドレス確認用フォーム画面ではメールアドレスと入力するフォームを表示します
  - パスワード再設定用フォーム画面ではパスワードと入力するフォームを入力用と確認用の2つを表示し、同じ値であることを確認します
  - ログインページにはメールアドレス確認用フォーム画面のリンクを表示します

---

## 4. パスワードリセット（一般ユーザー、管理者ユーザー）用のメールテンプレートを作成

パスワードリセット用の通知メールテンプレートを作成します。

- メールテンプレート（一般ユーザー）
  - パス: `/backend/laravel/resources/views/emails/password-reset.blade.php`
  - メール件名：パスワードリセットのお知らせ
- メールテンプレート（管理者ユーザー）
  - パス: `/backend/laravel/resources/views/emails/admin-password-reset.blade.php`
  - メール件名：管理者パスワードリセットのお知らせ

---

## 5. パスワードリセット（一般ユーザー、管理者ユーザー）用のルーティングを作成

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

---

## 6. パスワードリセット（一般ユーザー、管理者ユーザー）用の通知クラスを作成

一般ユーザーと管理者ユーザーのパスワードリセット用の通知クラスを作成します。

- 通知クラス（一般ユーザー）
  - クラス: `PasswordResetNotification`
  - パス: `/backend/laravel/app/Notifications/PasswordResetNotification.php`
- 通知クラス（管理者ユーザー）
  - クラス: `AdminPasswordResetNotification`
  - パス: `/backend/laravel/app/Notifications/AdminPasswordResetNotification.php`

---

## 7. パスワードリセット（一般ユーザー、管理者ユーザー）用のコントローラーを作成

パスワードリセット用のコントローラーを作成します。メールの送信、トークンの生成、バリデーション処理も含めて作成します。

- コントローラー（一般ユーザー）
  - メールアドレス確認
    - クラス: `PasswordForgotController`
    - パス: `/backend/laravel/app/Http/Controllers/PasswordForgotController.php`
    - メソッド
      - `index`
      - `sendMail`
  - パスワード再設定
    - クラス: `PasswordResetController`
    - パス: `/backend/laravel/app/Http/Controllers/PasswordResetController.php`
    - メソッド
      - `index`
      - `reset`
- コントローラー（管理者ユーザー）
  - メールアドレス確認
    - クラス: `AdminPasswordForgotController`
    - パス: `/backend/laravel/app/Http/Controllers/AdminPasswordForgotController.php`
    - メソッド
      - `index`
      - `sendMail`
  - パスワード再設定
    - クラス: `AdminPasswordResetController`
    - パス: `/backend/laravel/app/Http/Controllers/AdminPasswordResetController.php`
    - メソッド
      - `index`
      - `reset`

メール送信やトークン生成に伴い下記Laravelの設定ファイルも必要に応じて修正します。

- `/backend/laravel/config/mail.php`
- `/backend/laravel/config/auth.php`

---

## 8. パスワードリセット（一般ユーザー、管理者ユーザー）用のテスト

一般ユーザーと管理者ユーザーを使ってパスワードリセットのテストを行います。

---
