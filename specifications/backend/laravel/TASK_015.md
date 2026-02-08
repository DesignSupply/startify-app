---
title: バックエンド実装タスクリスト（Laravel）:汎用投稿CMS機能の実装
id: laravel_task_015
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---
<!-- markdownlint-disable MD025 -->
# バックエンド実装タスクリスト（Laravel）:汎用投稿CMS機能の実装

タイトルと本文テキストが投稿できる汎用的なCMS機能を実装します。一覧、詳細、編集ページなどの画面や投稿リスト用のページネーションも提供します。

---

## 1. 投稿管理機能のマイグレーションファイルを作成

投稿管理機能に必要となるマイグレーションファイルを作成、マイグレーションを実行しテーブルを作成します。

### 1.1. postsテーブルのマイグレーション

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_posts_table.php`
  - テーブル: `posts`
  - カラム
    - `id` / bigint / primary key / auto increment
    - `admin_user_id` / bigint / unsigned / not null / 外部キー: admin_users.id
    - `title` / varchar(255) / not null
    - `body` / text / not null
    - `author` / varchar(255) / not null / 表示用投稿者名
    - `published_at` / timestamp / not null
    - `is_deleted` / boolean / not null / default false / index
    - `deleted_at` / timestamp / nullable
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable
  - 外部キー制約
    - `admin_user_id` → admin_users.id / on delete cascade
  - インデックス制約
    - `admin_user_id`
    - `is_deleted`
    - `published_at`

**投稿の削除は `is_deleted` フラグによる論理削除とする**
**削除時に `deleted_at` に現在時刻を、復元時にはnullをセットする**
**drop手順において各カラムの `dropColumn` および外部キー制約の削除を追加**

### 1.2. categoriesテーブルのマイグレーション

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_categories_table.php`
  - テーブル: `categories`
  - カラム
    - `id` / bigint / primary key / auto increment
    - `name` / varchar(255) / not null
    - `slug` / varchar(255) / not null / unique
    - `is_deleted` / boolean / not null / default false / index
    - `deleted_at` / timestamp / nullable
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable
  - インデックス制約
    - `slug` / unique
    - `is_deleted`

**カテゴリの削除は `is_deleted` フラグによる論理削除とする**
**`slug` はURL用のユニークな識別子**

### 1.3. tagsテーブルのマイグレーション

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_tags_table.php`
  - テーブル: `tags`
  - カラム
    - `id` / bigint / primary key / auto increment
    - `name` / varchar(255) / not null
    - `slug` / varchar(255) / not null / unique
    - `is_deleted` / boolean / not null / default false / index
    - `deleted_at` / timestamp / nullable
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable
  - インデックス制約
    - `slug` / unique
    - `is_deleted`

**タグの削除は `is_deleted` フラグによる論理削除とする**
**`slug` はURL用のユニークな識別子**

### 1.4. category_postテーブルのマイグレーション（中間テーブル）

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_category_post_table.php`
  - テーブル: `category_post`
  - カラム
    - `category_id` / bigint / unsigned / not null / 外部キー: categories.id
    - `post_id` / bigint / unsigned / not null / 外部キー: posts.id
  - 外部キー制約
    - `category_id` → categories.id / on delete cascade
    - `post_id` → posts.id / on delete cascade
  - 複合主キー
    - `category_id`, `post_id`

#### 1.4.1. 注記

投稿とカテゴリの多対多リレーション用中間テーブル（アルファベット順）

### 1.5. post_tagテーブルのマイグレーション（中間テーブル）

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_post_tag_table.php`
  - テーブル: `post_tag`
  - カラム
    - `post_id` / bigint / unsigned / not null / 外部キー: posts.id
    - `tag_id` / bigint / unsigned / not null / 外部キー: tags.id
  - 外部キー制約
    - `post_id` → posts.id / on delete cascade
    - `tag_id` → tags.id / on delete cascade
  - 複合主キー
    - `post_id`, `tag_id`

#### 1.5.1. 注記

投稿とタグの多対多リレーション用中間テーブル

---

## 2. 投稿管理機能のモデルを作成

投稿管理機能に必要となるモデルを作成します。各モデルにリレーションと論理削除用のスコープを定義します。

### 2.1. Postモデルの作成

- モデル
  - パス: `/backend/laravel/app/Models/Post.php`
  - クラス: `Post`
  - 機能仕様
    - `$fillable`: `admin_user_id`, `title`, `body`, `author`, `published_at`
    - `$casts`: `published_at` → `datetime`, `is_deleted` → `boolean`
    - リレーション:
      - `adminUser()`: `belongsTo(AdminUser::class)` - 投稿者
      - `categories()`: `belongsToMany(Category::class)` - カテゴリ（多対多）
      - `tags()`: `belongsToMany(Tag::class)` - タグ（多対多）
    - スコープ:
      - `scopeActive($query)`: `is_deleted = false` のレコードのみ取得
      - `scopeOnlyDeleted($query)`: `is_deleted = true` のレコードのみ取得

**リレーションは中間テーブル名を明示せずにLaravel規約に従う**
**論理削除は一般ユーザーと同じ方式を採用**

### 2.2. Categoryモデルの作成

- モデル
  - パス: `/backend/laravel/app/Models/Category.php`
  - クラス: `Category`
  - 機能仕様
    - `$fillable`: `name`, `slug`
    - `$casts`: `is_deleted` → `boolean`
    - リレーション:
      - `posts()`: `belongsToMany(Post::class)` - 投稿（多対多）
    - スコープ:
      - `scopeActive($query)`: `is_deleted = false` のレコードのみ取得
      - `scopeOnlyDeleted($query)`: `is_deleted = true` のレコードのみ取得

**`slug` はURL用のユニークな識別子として使用**

### 2.3. Tagモデルの作成

- モデル
  - パス: `/backend/laravel/app/Models/Tag.php`
  - クラス: `Tag`
  - 機能仕様
    - `$fillable`: `name`, `slug`
    - `$casts`: `is_deleted` → `boolean`
    - リレーション:
      - `posts()`: `belongsToMany(Post::class)` - 投稿（多対多）
    - スコープ:
      - `scopeActive($query)`: `is_deleted = false` のレコードのみ取得
      - `scopeOnlyDeleted($query)`: `is_deleted = true` のレコードのみ取得

**`slug` はURL用のユニークな識別子として使用**

---

## 3. 投稿管理画面のビューを作成

投稿管理機能に必要となるページのビューを作成します。投稿・カテゴリ・タグそれぞれの管理画面を実装します。

### 3.1. 投稿管理ビューの作成

#### 3.1.1. 投稿一覧画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/posts/index.blade.php`
  - 機能仕様
    - リスト形式で投稿ID、タイトル、投稿者、公開日時、削除済みフラグの状態を表示
    - 各リストアイテム内に、詳細画面へのリンクを設置
    - 削除済み投稿も表示されるようにする
    - ページネーション対応（1ページ10件）
    - 公開日時の降順で表示
    - 新規投稿作成へのリンクを設置（管理者のみ表示）

#### 3.1.2. 投稿詳細画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/posts/show.blade.php`
  - 機能仕様
    - 投稿ID、タイトル、本文、投稿者、公開日時、カテゴリ、タグ、作成日時、更新日時を表示
    - 本文はHTMLタグを解釈して表示
    - カテゴリとタグはカンマ区切りで表示
    - 投稿編集画面へのリンクを設置（管理者のみ表示）
    - 一覧画面へ戻るリンクを設置

#### 3.1.3. 投稿作成画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/posts/create.blade.php`
  - 機能仕様
    - タイトル、本文、公開日時、カテゴリ（複数選択可）、タグ（複数選択可）を入力するフォームを配置
    - カテゴリとタグはチェックボックスで複数選択可能
    - 投稿者（author）は現在ログイン中の管理者名を自動設定（フォームには表示しない）
    - 公開日時はデフォルトで現在日時を設定
  - バリデーション
    - タイトル
      - 必須入力
      - 最大255文字
    - 本文
      - 必須入力
    - 公開日時
      - 必須入力
      - 日時形式

#### 3.1.4. 投稿編集画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/posts/edit.blade.php`
  - 機能仕様
    - タイトル、本文、公開日時、カテゴリ（複数選択可）、タグ（複数選択可）を編集するフォームを配置
    - フォームの各インプット初期値には登録されている値が入っている状態にする
    - カテゴリとタグはチェックボックスで、すでに紐付いているものはチェック済み状態
    - 投稿を更新すると、更新した日時が記録されるようにする
    - 削除済みでない場合には、該当投稿削除のボタンを設置
    - 削除済みの場合、復元ボタンを設置
  - バリデーション
    - 作成画面と同じバリデーションルール

### 3.2. カテゴリ管理ビューの作成

#### 3.2.1. カテゴリ一覧画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/categories/index.blade.php`
  - 機能仕様
    - リスト形式でカテゴリID、名前、スラッグ、削除済みフラグの状態を表示
    - 各リストアイテム内に、編集画面へのリンクを設置
    - 削除済みカテゴリも表示されるようにする
    - 新規カテゴリ作成へのリンクを設置

#### 3.2.2. カテゴリ作成画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/categories/create.blade.php`
  - 機能仕様
    - 名前、スラッグを入力するフォームを配置
  - バリデーション
    - 名前
      - 必須入力
      - 最大255文字
    - スラッグ
      - 必須入力
      - 最大255文字
      - 英数字とハイフンのみ
      - 他のカテゴリで登録済みのスラッグと重複不可

#### 3.2.3. カテゴリ編集画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/categories/edit.blade.php`
  - 機能仕様
    - 名前、スラッグを編集するフォームを配置
    - フォームの各インプット初期値には登録されている値が入っている状態にする
    - 削除済みでない場合には、該当カテゴリ削除のボタンを設置
    - 削除済みの場合、復元ボタンを設置
  - バリデーション
    - 作成画面と同じバリデーションルール

### 3.3. タグ管理ビューの作成

#### 3.3.1. タグ一覧画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/tags/index.blade.php`
  - 機能仕様
    - リスト形式でタグID、名前、スラッグ、削除済みフラグの状態を表示
    - 各リストアイテム内に、編集画面へのリンクを設置
    - 削除済みタグも表示されるようにする
    - 新規タグ作成へのリンクを設置

#### 3.3.2. タグ作成画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/tags/create.blade.php`
  - 機能仕様
    - 名前、スラッグを入力するフォームを配置
  - バリデーション
    - 名前
      - 必須入力
      - 最大255文字
    - スラッグ
      - 必須入力
      - 最大255文字
      - 英数字とハイフンのみ
      - 他のタグで登録済みのスラッグと重複不可

#### 3.3.3. タグ編集画面

- ビュー
  - パス: `/backend/laravel/resources/views/pages/admin/tags/edit.blade.php`
  - 機能仕様
    - 名前、スラッグを編集するフォームを配置
    - フォームの各インプット初期値には登録されている値が入っている状態にする
    - 削除済みでない場合には、該当タグ削除のボタンを設置
    - 削除済みの場合、復元ボタンを設置
  - バリデーション
    - 作成画面と同じバリデーションルール

**すべてのビューは既存のレイアウト `/backend/laravel/resources/views/layouts/default.blade.php` を使用する**
**管理者のみアクセス可能な画面は認証チェックをルーティング側で実施する**
**管理者権限で操作・閲覧するビューは `/pages/admin/` 配下に格納する**
**投稿の一覧と詳細は一般ユーザーにも開放するため `/pages/posts/` に配置、作成と編集は管理者専用のため `/pages/admin/posts/` に配置し、投稿関連のビューは2箇所に分散する**

---

## 4. 投稿管理機能のミドルウェアを作成

投稿閲覧機能で使用するカスタムミドルウェアを作成します。一般ユーザーまたは管理者のどちらかがログインしていればアクセス可能にします。

### 4.1. AuthenticateAnyミドルウェアの作成

- ミドルウェア
  - クラス: `AuthenticateAny`
  - パス: `/backend/laravel/app/Http/Middleware/AuthenticateAny.php`
  - 機能仕様
    - `Auth::guard('web')->check()` または `Auth::guard('admin')->check()` のどちらかが `true` であればアクセス許可
    - どちらもログインしていない場合は一般ユーザーログインページ（`/signin`）へリダイレクト

### 4.2. ミドルウェアの登録

- パス: `/backend/laravel/bootstrap/app.php`
- 機能仕様
  - `$middleware->alias()` に `'auth.any' => \App\Http\Middleware\AuthenticateAny::class` を追加

**投稿の一覧・詳細画面では `auth.any` ミドルウェアを使用することで、一般ユーザーと管理者の両方がアクセス可能にする**

---

## 5. 投稿管理機能のルーティングを作成

投稿管理機能で使用するルーティングを作成します。一般ユーザーと管理者でアクセス可能な画面を分けて定義します。

### 4.1. 投稿管理ルーティング

#### 5.1.1. 一般ユーザー + 管理者向けルーティング

- ルーティング
  - 一覧（画面表示）
    - パス: `/posts`
    - メソッド: `GET`
    - ルート名: `posts.index`
    - ミドルウェア: `auth.any`
  - 詳細（画面表示）
    - パス: `/posts/{id}`
    - メソッド: `GET`
    - ルート名: `posts.show`
    - ミドルウェア: `auth.any`

#### 5.1.3. 注記

一般ユーザー（users）と管理者（admin_users）の両方がアクセス可能

#### 5.1.2. 管理者専用ルーティング

- ルーティング
  - 作成（画面表示）
    - パス: `/admin/posts/create`
    - メソッド: `GET`
    - ルート名: `posts.create`
    - ミドルウェア: `auth:admin`
  - 作成（フォーム送信）
    - パス: `/admin/posts/store`
    - メソッド: `POST`
    - ルート名: `posts.store`
    - ミドルウェア: `auth:admin`
  - 編集（画面表示）
    - パス: `/admin/posts/{id}/edit`
    - メソッド: `GET`
    - ルート名: `posts.edit`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・更新）
    - パス: `/admin/posts/{id}/update`
    - メソッド: `POST`
    - ルート名: `posts.update`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・削除）
    - パス: `/admin/posts/{id}/delete`
    - メソッド: `POST`
    - ルート名: `posts.destroy`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・復元）
    - パス: `/admin/posts/{id}/restore`
    - メソッド: `POST`
    - ルート名: `posts.restore`
    - ミドルウェア: `auth:admin`

#### 5.1.4. 注記

管理者（admin_users）のみがアクセス可能

### 5.2. カテゴリ管理ルーティング（管理者専用）

- ルーティング
  - 一覧（画面表示）
    - パス: `/admin/categories`
    - メソッド: `GET`
    - ルート名: `categories.index`
    - ミドルウェア: `auth:admin`
  - 作成（画面表示）
    - パス: `/admin/categories/create`
    - メソッド: `GET`
    - ルート名: `categories.create`
    - ミドルウェア: `auth:admin`
  - 作成（フォーム送信）
    - パス: `/admin/categories/store`
    - メソッド: `POST`
    - ルート名: `categories.store`
    - ミドルウェア: `auth:admin`
  - 編集（画面表示）
    - パス: `/admin/categories/{id}/edit`
    - メソッド: `GET`
    - ルート名: `categories.edit`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・更新）
    - パス: `/admin/categories/{id}/update`
    - メソッド: `POST`
    - ルート名: `categories.update`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・削除）
    - パス: `/admin/categories/{id}/delete`
    - メソッド: `POST`
    - ルート名: `categories.destroy`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・復元）
    - パス: `/admin/categories/{id}/restore`
    - メソッド: `POST`
    - ルート名: `categories.restore`
    - ミドルウェア: `auth:admin`

### 5.3. タグ管理ルーティング（管理者専用）

- ルーティング
  - 一覧（画面表示）
    - パス: `/admin/tags`
    - メソッド: `GET`
    - ルート名: `tags.index`
    - ミドルウェア: `auth:admin`
  - 作成（画面表示）
    - パス: `/admin/tags/create`
    - メソッド: `GET`
    - ルート名: `tags.create`
    - ミドルウェア: `auth:admin`
  - 作成（フォーム送信）
    - パス: `/admin/tags/store`
    - メソッド: `POST`
    - ルート名: `tags.store`
    - ミドルウェア: `auth:admin`
  - 編集（画面表示）
    - パス: `/admin/tags/{id}/edit`
    - メソッド: `GET`
    - ルート名: `tags.edit`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・更新）
    - パス: `/admin/tags/{id}/update`
    - メソッド: `POST`
    - ルート名: `tags.update`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・削除）
    - パス: `/admin/tags/{id}/delete`
    - メソッド: `POST`
    - ルート名: `tags.destroy`
    - ミドルウェア: `auth:admin`
  - 編集（フォーム送信・復元）
    - パス: `/admin/tags/{id}/restore`
    - メソッド: `POST`
    - ルート名: `tags.restore`
    - ミドルウェア: `auth:admin`

**すべてのルーティングは `/backend/laravel/routes/web.php` に定義する**
**ミドルウェア `auth.any` は一般ユーザー（web）または管理者（admin）のどちらかがログインしていればアクセス可能**
**ミドルウェア `auth:admin` は管理者（admin）のみを許可**
**HTTPメソッドは `GET` と `POST` に限定する**

---

## 6. 投稿管理機能のリクエストクラスを作成

投稿管理機能のバリデーション用リクエストクラスを作成します。各機能のフォーム送信時のバリデーションルールを定義します。

### 6.1. PostStoreRequestの作成

- リクエストクラス
  - クラス: `PostStoreRequest`
  - パス: `/backend/laravel/app/Http/Requests/PostStoreRequest.php`
  - バリデーションルール:
    - `title`: `required|string|max:255`
    - `body`: `required|string`
    - `published_at`: `required|date`
    - `categories`: `nullable|array`
    - `categories.*`: `exists:categories,id`
    - `tags`: `nullable|array`
    - `tags.*`: `exists:tags,id`
  - 属性名（attributes）:
    - `title`: タイトル
    - `body`: 本文
    - `published_at`: 公開日時
    - `categories`: カテゴリ
    - `tags`: タグ
  - カスタムメッセージ（messages）:
    - `required`: :attributeは必須項目です。
    - `max`: :attributeは:max文字以内で入力してください。
    - `date`: :attributeの形式が正しくありません。
    - `exists`: 選択された:attributeは無効です。

### 6.2. PostUpdateRequestの作成

- リクエストクラス
  - クラス: `PostUpdateRequest`
  - パス: `/backend/laravel/app/Http/Requests/PostUpdateRequest.php`
  - バリデーションルール: `PostStoreRequest` と同じ
  - 属性名・カスタムメッセージ: `PostStoreRequest` と同じ

### 6.3. CategoryStoreRequestの作成

- リクエストクラス
  - クラス: `CategoryStoreRequest`
  - パス: `/backend/laravel/app/Http/Requests/CategoryStoreRequest.php`
  - バリデーションルール:
    - `name`: `required|string|max:255`
    - `slug`: `required|string|max:255|regex:/^[a-zA-Z0-9\-]+$/|unique:categories,slug`
  - 属性名（attributes）:
    - `name`: 名前
    - `slug`: スラッグ
  - カスタムメッセージ（messages）:
    - `required`: :attributeは必須項目です。
    - `max`: :attributeは:max文字以内で入力してください。
    - `regex`: :attributeは英数字とハイフンのみ使用できます。
    - `unique`: この:attributeはすでに使用されています。

### 6.4. CategoryUpdateRequestの作成

- リクエストクラス
  - クラス: `CategoryUpdateRequest`
  - パス: `/backend/laravel/app/Http/Requests/CategoryUpdateRequest.php`
  - バリデーションルール:
    - `name`: `required|string|max:255`
    - `slug`: `required|string|max:255|regex:/^[a-zA-Z0-9\-]+$/|unique:categories,slug,{id}`
      - 更新時は自分自身のIDを除外する必要がある
  - 属性名・カスタムメッセージ: `CategoryStoreRequest` と同じ

### 6.5. TagStoreRequestの作成

- リクエストクラス
  - クラス: `TagStoreRequest`
  - パス: `/backend/laravel/app/Http/Requests/TagStoreRequest.php`
  - バリデーションルール:
    - `name`: `required|string|max:255`
    - `slug`: `required|string|max:255|regex:/^[a-zA-Z0-9\-]+$/|unique:tags,slug`
  - 属性名（attributes）:
    - `name`: 名前
    - `slug`: スラッグ
  - カスタムメッセージ（messages）:
    - `required`: :attributeは必須項目です。
    - `max`: :attributeは:max文字以内で入力してください。
    - `regex`: :attributeは英数字とハイフンのみ使用できます。
    - `unique`: この:attributeはすでに使用されています。

### 6.6. TagUpdateRequestの作成

- リクエストクラス
  - クラス: `TagUpdateRequest`
  - パス: `/backend/laravel/app/Http/Requests/TagUpdateRequest.php`
  - バリデーションルール:
    - `name`: `required|string|max:255`
    - `slug`: `required|string|max:255|regex:/^[a-zA-Z0-9\-]+$/|unique:tags,slug,{id}`
      - 更新時は自分自身のIDを除外する必要がある
  - 属性名・カスタムメッセージ: `TagStoreRequest` と同じ

**すべてのリクエストクラスで `authorize()` メソッドは `return true;` を返す**
**既存のリクエストクラス（ContactFormRequestなど）のコーディングスタイルに準拠する**

---

## 7. 投稿管理機能のコントローラーを作成

投稿管理機能のコントローラーを作成します。各コントローラーにCRUD操作のメソッドを実装します。

### 7.1. PostControllerの作成

- コントローラー
  - クラス: `PostController`
  - パス: `/backend/laravel/app/Http/Controllers/PostController.php`
  - メソッド:
    - `index` - 投稿一覧（ページネーション10件/ページ、公開日時降順）
    - `show` - 投稿詳細
    - `create` - 投稿作成画面
    - `store` - 投稿作成処理
    - `edit` - 投稿編集画面
    - `update` - 投稿更新処理
    - `destroy` - 投稿削除処理（論理削除）
    - `restore` - 投稿復元処理
  - 機能仕様
    - `index`:
      - 管理者ログイン（`Auth::guard('admin')->check()`）がある場合：削除済み投稿も含めてすべて表示
      - 一般ユーザーのみの場合：削除済みでない投稿のみ表示（`Post::active()`）
      - ページネーション対応（10件/ページ）
    - `show`:
      - 管理者ログインがある場合：すべての投稿を表示可能
      - 一般ユーザーのみの場合：削除済みでない投稿のみ表示可能（`Post::active()`）
      - カテゴリとタグのリレーションをEager Loading
    - `create`: アクティブなカテゴリとタグを取得してビューに渡す
    - `store`: `PostStoreRequest` でバリデーション、ログイン中の管理者名を `author` に自動設定、カテゴリ・タグを紐付け
    - `edit`: 既存の投稿データ、アクティブなカテゴリとタグを取得してビューに渡す
    - `update`: `PostUpdateRequest` でバリデーション、カテゴリ・タグの紐付けを更新（`sync` メソッド使用）
    - `destroy`: `is_deleted = true`, `deleted_at = now()` で論理削除
    - `restore`: `is_deleted = false`, `deleted_at = null` で復元
    - 投稿作成・更新後は投稿一覧へリダイレクト
    - 投稿削除・復元後は投稿一覧へリダイレクト
    - 処理完了時にフラッシュメッセージを表示

### 7.2. CategoryControllerの作成

- コントローラー
  - クラス: `CategoryController`
  - パス: `/backend/laravel/app/Http/Controllers/CategoryController.php`
  - メソッド:
    - `index` - カテゴリ一覧
    - `create` - カテゴリ作成画面
    - `store` - カテゴリ作成処理
    - `edit` - カテゴリ編集画面
    - `update` - カテゴリ更新処理
    - `destroy` - カテゴリ削除処理（論理削除）
    - `restore` - カテゴリ復元処理
  - 機能仕様
    - `index`: 削除済みカテゴリも含めてすべて表示
    - `store`: `CategoryStoreRequest` でバリデーション
    - `update`: `CategoryUpdateRequest` でバリデーション
    - カテゴリ作成・更新後はカテゴリ一覧へリダイレクト
    - カテゴリ削除・復元後はカテゴリ一覧へリダイレクト
    - 処理完了時にフラッシュメッセージを表示

### 7.3. TagControllerの作成

- コントローラー
  - クラス: `TagController`
  - パス: `/backend/laravel/app/Http/Controllers/TagController.php`
  - メソッド:
    - `index` - タグ一覧
    - `create` - タグ作成画面
    - `store` - タグ作成処理
    - `edit` - タグ編集画面
    - `update` - タグ更新処理
    - `destroy` - タグ削除処理（論理削除）
    - `restore` - タグ復元処理
  - 機能仕様
    - `index`: 削除済みタグも含めてすべて表示
    - `store`: `TagStoreRequest` でバリデーション
    - `update`: `TagUpdateRequest` でバリデーション
    - タグ作成・更新後はタグ一覧へリダイレクト
    - タグ削除・復元後はタグ一覧へリダイレクト
    - 処理完了時にフラッシュメッセージを表示

**すべてのコントローラーで既存のコントローラー（AdminUsersControllerなど）のコーディングスタイルに準拠する**
**論理削除は一般ユーザー管理と同じ方式（`is_deleted`, `deleted_at`）を採用**
**Eager Loadingを適切に使用してN+1問題を回避する**

---

## 8. 投稿管理機能のシーダーを作成

投稿管理機能の開発用テストデータを作成するシーダーを作成します。

### 8.1. CategorySeederの作成

- シーダー
  - クラス: `CategorySeeder`
  - パス: `/backend/laravel/database/seeders/CategorySeeder.php`
  - 機能仕様
    - 開発用のカテゴリデータを作成
    - 5件のカテゴリを作成
    - 名前:「テストカテゴリ{index+1}」（例:テストカテゴリ1、テストカテゴリ2…テストカテゴリ5）
    - スラッグ:「test-category-{index+1}」（例:test-category-1、test-category-2…test-category-5）

### 8.2. TagSeederの作成

- シーダー
  - クラス: `TagSeeder`
  - パス: `/backend/laravel/database/seeders/TagSeeder.php`
  - 機能仕様
    - 開発用のタグデータを作成
    - 5件のタグを作成
    - 名前:「テストタグ{index+1}」（例:テストタグ1、テストタグ2…テストタグ5）
    - スラッグ:「test-tag-{index+1}」（例:test-tag-1、test-tag-2…test-tag-5）

### 8.3. PostSeederの作成

- シーダー
  - クラス: `PostSeeder`
  - パス: `/backend/laravel/database/seeders/PostSeeder.php`
  - 機能仕様
    - 開発用の投稿データを作成
    - 既存の管理者ユーザー（ID: 1）を投稿者として設定
    - 10件の投稿を作成
    - 各投稿にランダムにカテゴリとタグを紐付け
    - `published_at` は過去の日時を設定（新しい順）
    - タイトル:「テスト投稿{index+1}」（例: テスト投稿1、テスト投稿2...）
    - 本文:「テスト投稿{index+1}の本文テキストです」（例: テスト投稿1の本文テキストです）

### 8.4. DatabaseSeederへの登録

- シーダー
  - パス: `/backend/laravel/database/seeders/DatabaseSeeder.php`
  - 機能仕様
    - `$this->call()` に以下を追加:
      - `CategorySeeder::class`
      - `TagSeeder::class`
      - `PostSeeder::class`
    - 実行順序: UserSeeder → AdminUserSeeder → CategorySeeder → TagSeeder → PostSeeder

**シーダーはデータ作成のみを行い、テーブルのクリアは行わない**
**投稿の作成にはリレーション（`sync()`）を使用してカテゴリ・タグを紐付ける**
**既存のシーダー（UserSeeder、AdminUserSeederなど）のコーディングスタイルに準拠する**

---

## 9. 投稿管理機能のテスト

---
