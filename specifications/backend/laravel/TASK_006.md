---
title: バックエンド実装タスクリスト（Laravel）:新規ユーザー登録機能の実装
id: laravel_task_006
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:新規ユーザー登録機能の実装

ログイン認証ができる一般ユーザーを新規登録できる機能を実装します。

---

## 1. 新規ユーザー登録画面のビューを作成

新規ユーザー登録画面のビューを作成します。

- ビュー
  - パス（メールアドレス確認）: `/backend/laravel/resources/views/pages/signup/verify.blade.php`
  - パス（メール送信完了・確認待ち）: `/backend/laravel/resources/views/pages/signup/pending.blade.php`
  - パス（新規登録フォーム）: `/backend/laravel/resources/views/pages/signup/register.blade.php`
  - パス（登録完了）: `/backend/laravel/resources/views/pages/signup/complete.blade.php`

---

## 2. 新規ユーザー登録のメールテンプレートを作成

新規ユーザー登録のメールテンプレートを作成します。

- メールテンプレート
  - パス: `/backend/laravel/resources/views/emails/signup-verify.blade.php`
  - メール件名: 新規ユーザー登録のお知らせ
  - テンプレート形式: HTML

---

## 3. 新規ユーザー登録のルーティングを作成

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

---

## 4. 新規ユーザー登録の通知クラスを作成

新規ユーザー登録の通知クラスを作成します。

- 通知クラス
  - クラス: `SignUpNotification`
  - パス: `/backend/laravel/app/Notifications/SignUpNotification.php`

---

## 5. 新規ユーザー登録のコントローラーを作成

新規ユーザー登録のコントローラーを作成します。メールの送信、トークンの生成、バリデーション処理も含めて作成します。

- コントローラー
  - クラス: `SignUpController`
  - パス: `/backend/laravel/app/Http/Controllers/SignUpController.php`
  - メソッド
    - `index`
    - `verifyEmail`
    - `pending`
    - `verifyToken`
    - `form`
    - `register`
    - `complete`

メール送信やトークン生成に伴い下記Laravelの設定ファイルも必要に応じて修正します。

- `/backend/laravel/config/mail.php`
- `/backend/laravel/config/auth.php`

---

## 6. 新規ユーザー登録のテスト

新規ユーザー登録のテストを行います。

---
