---
title: バックエンド実装タスクリスト（Laravel）:コンタクトフォームの実装
id: laravel_task_011
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:コンタクトフォームの実装

コンタクトフォームの機能を実装します。

---

## 1.コンタクトフォーム画面のビューを作成

コンタクトフォーム画面のビューを作成します。

- ビュー（入力画面）
  - パス: `/backend/laravel/resources/views/pages/contact/index.blade.php`
  - 機能仕様
    - メールフォームを設置、確認画面へボタンで入力した値を遷移後の確認画面で表示
    - バリデーションエラーがある場合には、このページにリダイレクトさせる
    - 適切なセキュリティ対策を行う
  - フォーム項目
    - お名前: text
    - 会社名: text
    - メールアドレス: email
    - 電話番号: tel
    - ウェブサイトURL: url
    - お問い合わせ種別: checkbox
      - 種別1
      - 種別2
      - 種別3
    - 性別: radio
      - 男性
      - 女性
    - お問い合わせ内容: textarea
- ビュー（確認画面）
  - パス: `/backend/laravel/resources/views/pages/contact/confirm.blade.php`
  - 機能仕様
    - 入力値を表示、値がない箇所はそのまま空欄で
    - 送信するボタンと戻るボタンを設置、送信ボタンでメール送信と完了画面に遷移、戻るボタンで入力画面に遷移
    - 適切なセキュリティ対策を行う
- ビュー（完了画面）
  - パス: `/backend/laravel/resources/views/pages/contact/thanks.blade.php`
  - 機能仕様
    - メール送信が完了した内容のメッセージと、フロントページに戻るリンクを設置する

---

## 2. コンタクトフォームのメールテンプレートの作成

コンタクトフォーム用の通知メールテンプレートを作成します。

- メールテンプレート（自動返信メール）
  - パス: `/backend/laravel/resources/views/emails/contact-reply.blade.php`
  - メール件名: お問い合わせありがとうございます
  - テンプレート形式: HTML
- メールテンプレート（管理者メール）
  - パス: `/backend/laravel/resources/views/emails/contact-notification.blade.php`
  - メール件名: ウェブサイトからお問い合わせがありました
  - テンプレート形式: HTML

---

## 3. コンタクトフォーム機能のルーティングを作成

コンタクトフォーム機能のルーティングを作成します。

- ルーティング
  - 入力画面
    - パス: `/contact`
    - メソッド: `GET`
    - ルート名: `contact`
  - 確認フォーム
    - パス: `/contact/form`
    - メソッド: `POST`
    - ルート名: `contact.form`
  - 確認画面
    - パス: `/contact/confirm`
    - メソッド: `GET`
    - ルート名: `contact.confirm`
  - 送信フォーム
    - パス: `/contact/send`
    - メソッド: `POST`
    - ルート名: `contact.send`
  - 完了画面
    - パス: `/contact/thanks`
    - メソッド: `GET`
    - ルート名: `contact.thanks`

---

## 4. コンタクトフォーム用の通知クラスを作成

コンタクトフォーム用の通知クラスを作成します。

- 通知クラス（自動返信メール用）
  - クラス: `ContactFormReplyNotification`
  - パス: `/backend/laravel/app/Notifications/ContactFormReplyNotification.php`
- 通知クラス（管理者メール用）
  - クラス: `ContactFormAdminNotification`
  - パス: `/backend/laravel/app/Notifications/ContactFormAdminNotification.php`

---

## 5. コンタクトフォーム用のリクエストクラスを作成

コンタクトフォーム用のリクエストクラスを作成します。

- リクエストクラス
  - クラス: `ContactFormRequest`
  - パス: `/backend/laravel/app/Http/Request/ContactFormRequest.php`
  - バリデーション
    - お名前
      - 必須入力
      - 最大255文字
    - 会社名
      - 最大255文字
    - メールアドレス
      - メールアドレス形式
      - 必須入力
    - 電話番号
      - 電話番号形式
    - ウェブサイトURL
      - URL形式
    - お問い合わせ内容
      - 必須入力

---

## 6. コンタクトフォーム機能のコントローラーを作成

コンタクトフォーム機能のコントローラーを作成します。

- コントローラー
  - クラス: `ContactController`
  - パス: `/backend/laravel/app/Http/Controllers/ContactController.php`
  - メソッド: 
    - `index`
    - `confirm`
    - `thanks`
    - `form`
    - `send`
  - 機能仕様
    - 確認画面と完了画面にフォーム処理を経由せずに直接アクセスした場合には `/contact` にリダイレクトさせる 
    - メールは自動返信メールと管理者メールの2通を送信する、メール本文にはフォーム入力値が表示されるようにする
    - バリデーションエラーのメッセージは日本語で表示させる
    - 管理者メールの宛先は `admin@example.com` とする
    - お問い合わせの内容はデータベースに保存しないものとする

---

## 7. コンタクトフォーム機能のテスト

---
