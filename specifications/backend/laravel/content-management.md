---
title: Laravelアプリケーション コンテンツ管理仕様
status: current
last_updated: 2026-08-20
related_paths:
  - backend/laravel/.env.example
  - backend/laravel/app/Http/Controllers/PostController.php
  - backend/laravel/app/Http/Controllers/CategoryController.php
  - backend/laravel/app/Http/Controllers/TagController.php
  - backend/laravel/app/Http/Middleware/AuthenticateAny.php
  - backend/laravel/app/Http/Requests/PostStoreRequest.php
  - backend/laravel/app/Http/Requests/PostUpdateRequest.php
  - backend/laravel/app/Http/Requests/CategoryStoreRequest.php
  - backend/laravel/app/Http/Requests/CategoryUpdateRequest.php
  - backend/laravel/app/Http/Requests/TagStoreRequest.php
  - backend/laravel/app/Http/Requests/TagUpdateRequest.php
  - backend/laravel/app/Models/Post.php
  - backend/laravel/app/Models/Category.php
  - backend/laravel/app/Models/Tag.php
  - backend/laravel/config/app.php
  - backend/laravel/database/migrations/
  - backend/laravel/database/seeders/
  - backend/laravel/resources/views/pages/posts/
  - backend/laravel/resources/views/pages/admin/posts/
  - backend/laravel/resources/views/pages/admin/categories/
  - backend/laravel/resources/views/pages/admin/tags/
  - backend/laravel/routes/web.php
  - backend/laravel/tests/
---

# Laravelアプリケーション コンテンツ管理仕様

Startify-AppのLaravelアプリケーションが提供する汎用投稿、カテゴリ、タグの閲覧・管理機能について、現在の実装と維持する実装規約を定義します。

本書はコンテンツ管理の画面動作、入力、Relation、公開・削除状態を扱います。画面とRouteの横断的な索引は `specifications/backend/laravel/screens-and-features.md`、物理Schemaは `specifications/backend/laravel/database.md`、認証Guardは `specifications/backend/laravel/authentication.md` を参照してください。

## 1. 機能の構成

現在のコンテンツ管理はLaravel MPAとして実装され、次の3種類のModelを扱います。

| Model | 用途 | 主なRelation |
| --- | --- | --- |
| `Post` | タイトル、本文、投稿者、公開日時を持つ汎用投稿 | 管理者と多対1、カテゴリ・タグと多対多 |
| `Category` | 投稿のカテゴリ分類 | 投稿と多対多 |
| `Tag` | 投稿のタグ分類 | 投稿と多対多 |

投稿一覧・詳細は、一般ユーザーまたは管理者のSession認証が必要です。投稿の作成・更新・論理削除・復元と、カテゴリ・タグの管理は管理者専用です。

コンテンツ管理用のJSON APIは現在存在しません。Next.jsの投稿取得仕様とは独立したLaravel MPA機能です。

## 2. Routeとアクセス境界

### 2.1 投稿閲覧

| Method / Path | Route Name | Controller Action | Middleware | 用途 |
| --- | --- | --- | --- | --- |
| `GET /posts` | `posts.index` | `PostController@index` | `auth.any` | 投稿一覧 |
| `GET /posts/{id}` | `posts.show` | `PostController@show` | `auth.any` | 投稿詳細 |

`auth.any` は、`web` Guardの一般ユーザーまたは `admin` Guardの管理者のどちらかが認証済みであればアクセスを許可します。未認証の場合は一般ユーザーログイン画面へRedirectします。

### 2.2 投稿管理

| Method / Path | Route Name | Action | 用途 |
| --- | --- | --- | --- |
| `GET /admin/posts/create` | `posts.create` | `create` | 作成画面 |
| `POST /admin/posts/store` | `posts.store` | `store` | 作成処理 |
| `GET /admin/posts/{id}/edit` | `posts.edit` | `edit` | 編集画面 |
| `POST /admin/posts/{id}/update` | `posts.update` | `update` | 更新処理 |
| `POST /admin/posts/{id}/delete` | `posts.destroy` | `destroy` | 論理削除 |
| `POST /admin/posts/{id}/restore` | `posts.restore` | `restore` | 復元 |

### 2.3 カテゴリ・タグ管理

カテゴリとタグには、それぞれ次の管理者専用Routeがあります。

| Resource | Method / Path | Route Name | Controller Action | 用途 |
| --- | --- | --- | --- | --- |
| カテゴリ | `GET /admin/categories` | `categories.index` | `CategoryController@index` | 一覧 |
| カテゴリ | `GET /admin/categories/create` | `categories.create` | `CategoryController@create` | 作成画面 |
| カテゴリ | `POST /admin/categories/store` | `categories.store` | `CategoryController@store` | 作成処理 |
| カテゴリ | `GET /admin/categories/{id}/edit` | `categories.edit` | `CategoryController@edit` | 編集画面 |
| カテゴリ | `POST /admin/categories/{id}/update` | `categories.update` | `CategoryController@update` | 更新処理 |
| カテゴリ | `POST /admin/categories/{id}/delete` | `categories.destroy` | `CategoryController@destroy` | 論理削除 |
| カテゴリ | `POST /admin/categories/{id}/restore` | `categories.restore` | `CategoryController@restore` | 復元 |
| タグ | `GET /admin/tags` | `tags.index` | `TagController@index` | 一覧 |
| タグ | `GET /admin/tags/create` | `tags.create` | `TagController@create` | 作成画面 |
| タグ | `POST /admin/tags/store` | `tags.store` | `TagController@store` | 作成処理 |
| タグ | `GET /admin/tags/{id}/edit` | `tags.edit` | `TagController@edit` | 編集画面 |
| タグ | `POST /admin/tags/{id}/update` | `tags.update` | `TagController@update` | 更新処理 |
| タグ | `POST /admin/tags/{id}/delete` | `tags.destroy` | `TagController@destroy` | 論理削除 |
| タグ | `POST /admin/tags/{id}/restore` | `tags.restore` | `TagController@restore` | 復元 |

投稿、カテゴリ、タグの管理Routeは `auth:admin` Middleware内にあります。POST FormはBladeの `@csrf` を使用し、Laravel標準のCSRF検証を受けます。IDを受け取るRouteには `whereNumber('id')` を指定します。

## 3. 投稿データ

投稿が保持するアプリケーション固有の値は次のとおりです。

| Field | 用途 |
| --- | --- |
| `admin_user_id` | 作成した管理者のID |
| `author` | 作成時点の管理者名を保存する表示用Snapshot |
| `title` | 投稿タイトル |
| `body` | `TEXT`型の投稿本文 |
| `published_at` | 画面上の公開日時 |
| `is_deleted` | 独自論理削除Flag |
| `deleted_at` | 独自論理削除日時 |

`published_at` はEloquentで `datetime`、`is_deleted` は `boolean` にCastします。`admin_user_id` の参照先となる管理者が物理削除された場合、DBの外部キーにより投稿も物理削除されます。`author` はSnapshotのため、管理者名を後から変更しても自動更新されません。

日時処理はLaravel ApplicationのTimezoneを基準にします。`config/app.php` は `APP_TIMEZONE` を参照し、未設定時は `UTC` を使用します。現在の `.env.example` は `APP_TIMEZONE=Asia/Tokyo` です。作成画面の公開日時初期値に使用する `now()` もApplication Timezoneに従いますが、実際の環境では環境変数によって変更できます。

投稿とカテゴリは `category_post`、投稿とタグは `post_tag` を介した多対多Relationです。中間テーブルは複合Primary Keyを持ち、Timestampは持ちません。

## 4. 投稿一覧・詳細

### 4.1 一覧

`PostController@index` は認証中のGuardによって取得範囲を切り替えます。

| 利用者 | 取得範囲 |
| --- | --- |
| 管理者 | 論理削除済みを含むすべての投稿 |
| 一般ユーザー | `Post::active()` により `is_deleted = false` の投稿 |

一覧は `published_at` の降順で並べ、1ページ10件でPaginationします。ID、タイトル、表示用投稿者名、公開日時、削除状態を表示します。削除状態列は一般ユーザーにも表示されますが、一般ユーザー向けQueryは有効な投稿だけを取得するため常に未削除表示です。新規投稿作成Linkは管理者にだけ表示します。

現在の一般ユーザー向けQueryは `published_at` を公開条件として使用しないため、未来日時の投稿も表示対象になります。この改善はIssue #25で管理します。

### 4.2 詳細

管理者は論理削除済みを含む投稿を取得し、一般ユーザーは `Post::active()` を通した投稿だけを取得します。存在しないIDまたは一般ユーザーが指定した論理削除済み投稿は404になります。

投稿詳細では、カテゴリとタグをEager Loadingし、タイトル、本文、投稿者、公開日時、カテゴリ、タグ、作成・更新日時、削除状態を表示します。

本文は次の順序で処理し、入力されたHTMLをそのまま解釈しません。

1. `e()` でHTML Escapeする
2. `nl2br()` で改行をHTMLの改行へ変換する

カテゴリ名とタグ名もBladeの通常のEscape出力を使用します。現在のRelation Queryはカテゴリ・タグの論理削除状態を絞り込まないため、論理削除済み分類も一般ユーザーへ表示されます。この改善はIssue #26で管理します。

## 5. 投稿の作成・更新

### 5.1 作成

作成画面は、有効なカテゴリとタグを名前順で取得し、複数選択のCheckboxとして表示します。公開日時の初期値は画面表示時の現在日時です。

作成処理は `PostStoreRequest` の検証済みDataを使用し、認証中の管理者から次を設定します。

- `admin_user_id`: 管理者ID
- `author`: 管理者名

投稿保存後、カテゴリとタグが送信されている場合は、それぞれ `sync()` でRelationを設定します。作成・Relation同期は現在Transactionで囲まれていません。

### 5.2 更新

編集画面は投稿と全Relationを取得し、選択肢には有効なカテゴリとタグだけを表示します。タイトル、本文、公開日時、カテゴリ、タグを更新できます。`admin_user_id` と `author` は更新しません。

更新時は投稿を保存した後、送信されたカテゴリ・タグIDを `sync()` します。未選択の場合は空配列を同期し、既存Relationを解除します。投稿保存と2つのRelation同期は現在Transactionで囲まれていません。

論理削除済み分類は編集画面の選択肢に出ませんが既存Relationには含まれるため、投稿更新時に意図せずRelationが解除される可能性があります。この改善はIssue #26、Service・Transactionの整理はIssue #11で管理します。

## 6. カテゴリ・タグ管理

カテゴリとタグは、同じ構造と管理フローを持ちます。

| Field | 現在の用途・制約 |
| --- | --- |
| `name` | 表示名、最大255文字、DB上は重複可能 |
| `slug` | 英数字とハイフン、最大255文字、カテゴリ・タグそれぞれのTable内でUnique |
| `is_deleted` | 独自論理削除Flag |
| `deleted_at` | 独自論理削除日時 |

一覧は論理削除済みを含むすべてのRecordを名前順で取得し、Paginationは行いません。作成・更新ではForm Requestの検証済みDataを保存します。

`slug` は将来URL用識別子として利用できる形式ですが、現在の投稿Route、表示、絞り込みでは使用していません。論理削除後もRecordとUnique制約が残るため、削除済みRecordと同じSlugを新規作成できません。

## 7. 現在のValidation

### 7.1 投稿

`PostStoreRequest` と `PostUpdateRequest` は同じRuleを使用します。

| Field | 現在のRule | 必須 |
| --- | --- | --- |
| `title` | 文字列、最大255文字 | 必須 |
| `body` | 文字列、最大長なし | 必須 |
| `published_at` | 日付として解釈可能 | 必須 |
| `categories` | 配列 | 任意 |
| `categories.*` | `categories.id` に存在 | 任意 |
| `tags` | 配列 | 任意 |
| `tags.*` | `tags.id` に存在 | 任意 |

現在は本文の最大文字数、カテゴリ・タグの選択件数、IDの整数形式・重複・配列構造を制限しません。`body` のDB型は最大65,535バイトの `TEXT` ですが、Nginxの `client_max_body_size` とPHPの `post_max_size` は64MBであり、DBへ保存できない本文もHTTP層では受信できます。

投稿作成・編集画面が直接表示するErrorも `title`、`body`、`published_at` に限られます。本文10,000文字、カテゴリ・タグ各10件などの改善はIssue #27で管理します。

論理削除済みカテゴリ・タグもDBには存在するため、現在の `exists` Ruleを通過します。この改善はIssue #26で管理します。

### 7.2 カテゴリ・タグ

作成時の `slug` は対象Table全体でUnique、更新時はRouteのIDをUnique判定から除外します。

| Field | 現在のRule | 必須 |
| --- | --- | --- |
| `name` | 文字列、最大255文字 | 必須 |
| `slug` | 文字列、最大255文字、英数字とハイフン、Unique | 必須 |

すべてのForm Requestの `authorize()` は `true` を返します。操作権限はRouteの `auth:admin` Middlewareで制御します。

## 8. 論理削除と復元

投稿、カテゴリ、タグはLaravel標準の `SoftDeletes` を使用せず、Controllerが次の状態を明示的に更新します。

| 状態 | `is_deleted` | `deleted_at` |
| --- | --- | --- |
| 有効 | `false` | `null` |
| 削除済み | `true` | 削除日時 |

各Modelは `active()` と `onlyDeleted()` Scopeを持ちます。Global Scopeはないため、通常Queryは論理削除済みRecordを自動的に除外しません。

カテゴリ・タグの論理削除時に、中間テーブルのRelationは削除しません。DBのCascade Deleteは物理削除にだけ適用されます。論理削除済みRelationの表示、投稿更新時の維持、復元後の再利用方針はIssue #26で管理します。

## 9. Seeder

開発用Seederは、カテゴリ5件、タグ5件、投稿10件を作成します。投稿者には最初の管理者を使用し、各投稿へ1〜3件のカテゴリとタグをランダムに関連付けます。公開日時には過去日時を設定します。

`DatabaseSeeder` は `AdminUserSeeder` の後に `CategorySeeder`、`TagSeeder`、`PostSeeder` を実行します。Seederは既存Dataを削除せず、重複実行に対する冪等性を保証しません。本番環境の初期Data投入には使用しません。

## 10. 実装時の共通方針

コンテンツ管理を変更するときは、次を基本とします。

- 閲覧Queryでは、利用者種別、公開状態、論理削除状態を明示する
- 管理処理は `auth:admin` とCSRF検証の内側へ置く
- Request DataはForm Requestで検証し、`validated()` の結果を使用する
- 利用者入力をHTMLへ出力するときはEscapeする
- 複数Tableを変更する処理は、失敗時の整合性を考慮してTransaction境界を決める
- 論理削除と物理削除を区別し、RelationとUnique制約への影響を明示する
- DB型、文字コード、入力上限を整合させる
- 一覧、詳細、作成、更新、削除、復元、認証境界をFeature Testで確認する

これらは維持・採用する実装規約です。現在実装との不一致は、次の既知Issueで管理します。

## 11. 既知の課題

以下は本書作成時に確認済みのコンテンツ管理課題です。改善後は本文を現在仕様へ更新し、解決済みIssueをこの一覧から削除します。

| Issue | 現在の課題 |
| --- | --- |
| [#11](https://github.com/DesignSupply/startify-app/issues/11) | 投稿作成・更新、Relation同期、論理削除・復元を含むControllerの責務とTransaction境界が整理されていない |
| [#25](https://github.com/DesignSupply/startify-app/issues/25) | `published_at` が一般ユーザー向け閲覧条件に使われず、未来日時の投稿も表示される |
| [#26](https://github.com/DesignSupply/startify-app/issues/26) | 論理削除済みカテゴリ・タグが一般ユーザーへ表示され、直接POSTで関連付けでき、投稿更新時に既存Relationが失われる可能性がある |
| [#27](https://github.com/DesignSupply/startify-app/issues/27) | 本文、カテゴリ・タグ配列の上限・型・重複・構造Validationと画面上のError表示が不足している |

次の項目も現在実装の制約です。関連領域の仕様作成または実装変更時に再評価します。

- コンテンツ管理用のJSON APIがない
- 投稿、カテゴリ、タグのFeature Testがない
- カテゴリ・タグ一覧にPaginationがない
- カテゴリ・タグのSlugをRouteや絞り込みに使用していない

## 12. 検証

Docker環境起動後、`server/` でコンテナー状態、Route、Migration、テストを確認します。

```bash
make ps
```

```bash
make laravel-route
```

```bash
docker compose exec app bash -c "cd /var/www/html/laravel && php artisan migrate:status"
```

```bash
make laravel-test
```

現在の自動テストは基本Test 2件だけで、投稿、カテゴリ、タグの閲覧・管理動作を直接検証しません。調査・レビューだけの場合は、Migration適用、Seeder実行、コンテンツDataの作成・更新・削除を行いません。

## 13. 移行元資料

本書は、次の既存資料から設計意図を抽出し、現在のRoute、Middleware、Controller、Form Request、Model、Migration、Seeder、Blade、テストと照合して再構成しています。

- `specifications/backend/laravel/TASK_015.md`
- `.cursor/rules/app-overview.mdc`
- `.cursor/rules/dev-backend.mdc`

これらはドキュメント移行が完了するまで設計意図の確認に使用しますが、Laravelのコンテンツ管理に関する現在仕様としては本書と現在の実装を優先します。
