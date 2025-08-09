---
title: バックエンド実装タスクリスト（Laravel）:一般ユーザー管理機能の実装
id: laravel_task_013
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:一般ユーザー管理機能の実装

管理者ユーザー向けに一般ユーザーの管理機能を実装します。一般ユーザーの登録情報の更新やアカウント削除を行います。

---

## 1. 一般ユーザー管理機能のマイグレーションファイルを作成

仕様変更にともなうマイグレーションファイルを作成、マイグレーションを実行しテーブルを更新します。

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_update_users_table.php`
  - テーブル: `users`
  - カラム
    - `is_deleted` / boolean / not null / default false / index
    - `deleted_at` / timestamp / nullable

**アカウント削除は `is_deleted` フラグによる論理削除とする**
**アカウント削除時に `deleted_at` に現在時刻を、復元時にはnullをセットする**
**drop手順において `is_deleted` と `deleted_at` の `dropColumn` を追加**

---

## 2. 一般ユーザー管理機能追加に伴う既存モデルの更新

一般ユーザー管理機能追加に伴い、`/backend/laravel/app/Models/User.php` を下記のように更新します。

```php

protected $casts = [
    'email_verified_at' => 'datetime',
    'is_deleted' => 'boolean', // 追加
];

public function scopeActive($query)
{
    return $query->where('is_deleted', false);
} // 追加

public function scopeOnlyDeleted($query)
{
    return $query->where('is_deleted', true);
} // 追加

```

---

## 3. 一般ユーザー管理画面のビューを作成

一般ユーザー管理機能に必要となるページのビューを作成します。

- ビュー（一覧画面）
  - パス: `/backend/laravel/resources/views/pages/admin/users/index.blade.php`
  - 機能仕様
    - リスト形式でユーザーID、ユーザー名、削除済みフラグの状態を表示
    - 各リストアイテム内に、それぞれ詳細画面へのリンクを設置
    - 削除済みユーザーも表示されるようにする
- ビュー（詳細画面）
  - パス: `/backend/laravel/resources/views/pages/admin/users/show.blade.php`
  - 機能仕様
    - ユーザーID、ユーザー名、メールアドレス、作成日時を表示
    - ユーザー情報編集画面へのリンクを設置
- ビュー（編集画面）
  - パス: `/backend/laravel/resources/views/pages/admin/users/edit.blade.php`
  - 機能仕様
    - 画面上にはユーザー名、メールアドレス、パスワード、を編集するためのフォームを配置
    - フォームの各インプット初期値には登録されている値が入っている状態にする
    - プロフィールを更新すると、更新した日時が記録されるようにする
    - 一般ユーザー編集で使用しているビューと同じUI構成となるように踏襲する。
    - 削除済みでない場合には、該当ユーザー削除のボタンを設置
    - 削除済みの場合、復元ボタンを設置
  - バリデーション
    - ユーザー名
      - 必須入力
      - 最大255文字
    - メールアドレス
      - メールアドレス形式
      - 他ユーザーで登録済みのメールアドレスに変更することは不可
      - 必須入力
    - パスワード
      - 8文字以上

---

## 4. 一般ユーザー管理機能のルーティングを作成

一般ユーザー管理機能で使用するルーティングを作成します。今回はすべて管理者認証ルーティング配下で管理されるものとします。

- ルーティング
  - 一覧（画面表示）
    - パス: `/admin/users`
    - メソッド: `GET`
    - ルート名: `admin.users.index`
  - 詳細（画面表示）
    - パス: `/admin/users/{id}`
    - メソッド: `GET`
    - ルート名: `admin.users.show`
  - 編集（画面表示）
    - パス: `/admin/users/{id}/edit`
    - メソッド: `GET`
    - ルート名: `admin.users.edit`
  - 編集（フォーム送信・更新）
    - パス: `/admin/users/{id}/update`
    - メソッド: `POST`
    - ルート名: `admin.users.update`
  - 編集（フォーム送信・削除）
    - パス: `/admin/users/{id}/delete`
    - メソッド: `POST`
    - ルート名: `admin.users.destroy`
  - 編集（フォーム送信・復元）
    - パス: `/admin/users/{id}/restore`
    - メソッド: `POST`
    - ルート名: `admin.users.restore`

---

## 5. 一般ユーザー管理機能のコントローラーを作成

一般ユーザー管理機能のコントローラーを作成します。

- コントローラー
  - クラス: `AdminUsersController`
  - パス: `/backend/laravel/app/Http/Controllers/AdminUsersController.php`
  - メソッド:
    - `index`
    - `show`
    - `edit`
    - `update`
    - `destroy`
    - `restore`
  - 機能仕様
    - ユーザー情報更新後はユーザー一覧画面へリダイレクトさせます
    - ユーザー情報削除後はユーザー一覧画面へリダイレクトさせます
    - ユーザー情報復元後はユーザー一覧画面へリダイレクトさせます
    - ユーザー情報の更新、削除、復元の完了時にはそれぞれ処理完了のメッセージを表示させます
    - 管理者ログイン未認証の場合には `/admin` へリダイレクトさせます
    - ユーザーの削除時には `is_deleted = true` 、 `deleted_at = now()` とします
    - ユーザーの復元時には `is_deleted = false` 、 `deleted_at = null` とします
    - ログイン中のユーザーが削除された場合には、 `sessions` テーブルから対象の `user_id` のレコードを削除し、強制ログアウトさせます

---

## 6. ログイン、パスワードリセットのバリデーション処理修正

ユーザー削除機能が追加されたことによる既存処理の修正を行います。

- `/backend/laravel/app/Http/Controllers/SignInController.php` の `signIn` メソッドに削除ユーザーチェックとバリデーション処理を追加します

```php

// 削除ユーザーの場合
if ($user->is_deleted) {
    return redirect()->route('signin')->withErrors([
        'email' => 'このアカウントは削除されています。',
    ]);
}

```

- `/backend/laravel/app/Http/Controllers/PasswordForgotController.php` の `sendMail` メソッドに削除ユーザーチェックとバリデーション処理を追加します

```php

// 削除ユーザーの場合
if ($user->is_deleted) {
    return back()->withErrors([
        'email' => '削除済みアカウントのパスワードリセットはできません',
    ]);
}

```

- `/backend/laravel/app/Http/Controllers/PasswordResetController.php` の `reset` メソッドに削除ユーザーチェックとバリデーション処理を追加します

```php

// 削除ユーザーの場合
if ($user->is_deleted) {
    return back()->withErrors([
        'email' => '削除済みアカウントのパスワードリセットはできません',
    ]);
}

```

---

## 7. 一般ユーザー管理機能のテスト

---
