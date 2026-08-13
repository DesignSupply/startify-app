---
title: Laravelアプリケーション アーキテクチャ仕様
status: current
last_updated: 2026-08-13
related_paths:
  - backend/laravel/bootstrap/app.php
  - backend/laravel/routes/
  - backend/laravel/app/Console/Commands/
  - backend/laravel/app/Helpers/
  - backend/laravel/app/Http/Controllers/
  - backend/laravel/app/Http/Middleware/
  - backend/laravel/app/Http/Requests/
  - backend/laravel/app/Models/
  - backend/laravel/app/Notifications/
  - backend/laravel/app/Services/
  - backend/laravel/database/migrations/
  - backend/laravel/database/seeders/
  - backend/laravel/resources/views/
  - backend/laravel/tests/
---

# Laravelアプリケーション アーキテクチャ仕様

Startify-AppのLaravelアプリケーションにおける、現在のレイヤー構成、配置、命名、ルーティング、Blade構成と、機能追加・変更時に確認する対象を定義します。

本書は共通の構造と責務を扱います。画面と機能、データベース、認証、問い合わせとメール、コンテンツ管理、ファイル管理、バリデーションとセキュリティの詳細は、個別仕様へ順次分離します。

## 1. アプリケーション構成

現在のLaravelアプリケーションは、次の要素で構成されています。

| 要素 | 主な配置 | 現在の責務 |
| --- | --- | --- |
| Route | `backend/laravel/routes/` | URL、HTTP Method、Middleware、Controller Actionの接続 |
| Controller | `backend/laravel/app/Http/Controllers/` | HTTP入力、画面遷移、処理の組み立て、Responseの返却 |
| API Controller | `backend/laravel/app/Http/Controllers/Api/` | JSON APIの入力とResponse |
| Form Request | `backend/laravel/app/Http/Requests/` | 一部フォームの入力検証、属性名、エラーメッセージ |
| Middleware | `backend/laravel/app/Http/Middleware/` | 認証、JWT検証、API Request Guard |
| Model | `backend/laravel/app/Models/` | EloquentによるDB操作、Relation、Cast、Query Scope |
| Service | `backend/laravel/app/Services/` | ファイル処理など、再利用する副作用を伴う処理 |
| Helper | `backend/laravel/app/Helpers/` | 値の変換や判定などの補助処理 |
| Notification | `backend/laravel/app/Notifications/` | メールChannel、件名、Templateへのデータ受け渡し |
| Console Command | `backend/laravel/app/Console/Commands/` | 手動または定期実行する保守処理 |
| Blade View | `backend/laravel/resources/views/` | Layout、Component、Page、メール本文 |
| Migration | `backend/laravel/database/migrations/` | DB Schema、Index、外部キー制約 |
| Seeder | `backend/laravel/database/seeders/` | 開発用初期データ |
| Test | `backend/laravel/tests/` | Unit TestとFeature Test |

機能を追加・変更する際は、処理を置く場所を既存の責務に合わせます。機能固有の詳細な処理を本書へ複製せず、該当する機能仕様を正本とします。

## 2. ルーティング

Laravel 11のRoute読込とMiddleware Aliasは `backend/laravel/bootstrap/app.php` で設定します。`backend/laravel/app/Http/Kernel.php` は現在の構成に存在しません。

- BladeによるMPAは `backend/laravel/routes/web.php` に定義する
- JSON APIは `backend/laravel/routes/api.php` に定義する
- Console Routeは `backend/laravel/routes/console.php` に定義する
- WebとAPIのControllerは、Namespace Groupと文字列形式のController指定を使用する
- `prefix()` で機能単位のURLをまとめる
- `middleware()` で認証・認可境界をまとめる
- Routeには参照用の名前を付ける
- 数値IDのRouteでは、現在一部のRouteに `whereNumber('id')` を指定する

現在のWeb RouteはGETとPOSTだけを使用し、変更操作をURLとAction名で区別します。CRUD系機能の主なパターンは次のとおりです。

| 操作 | Method | URL Pattern | Controller Action |
| --- | --- | --- | --- |
| 一覧 | `GET` | `/resources` | `index` |
| 詳細 | `GET` | `/resources/{id}` | `show` |
| 作成画面 | `GET` | `/resources/create` | `create` |
| 作成 | `POST` | `/resources/store` | `store` |
| 編集画面 | `GET` | `/resources/{id}/edit` | `edit` |
| 更新 | `POST` | `/resources/{id}/update` | `update` |
| 削除 | `POST` | `/resources/{id}/delete` | `destroy` |
| 復元 | `POST` | `/resources/{id}/restore` | `restore` |

この表は現在実装の共通パターンです。すべての機能が全Actionを持つわけではありません。新規実装でHTTP MethodやURL規則を変更する場合は、既存クライアント、Blade Form、Middleware、CSRF、テストへの影響を確認します。

## 3. Controller

ControllerはRequestを受け取り、必要な処理を組み立てて、View、RedirectまたはJSON Responseを返します。現在のControllerには、次の処理が存在します。

- 入力検証
- Session Guardによる認証と権限確認
- Modelの取得・作成・更新
- Sessionへの一時データ保存と削除
- Notificationの送信
- Storage操作
- View Dataの構築
- RedirectまたはResponseの返却

現在はController内に直接処理を持つ機能と、Form Request、Service、Helperへ一部を分離した機能が併存しています。既存機能を変更するときは、その機能で使われている構造を確認し、無関係なレイヤー再編を同じ変更へ混ぜません。

一覧、詳細、作成、編集、更新、削除、復元には、既存の `index`、`show`、`create`、`edit`、`store`、`update`、`destroy`、`restore` を使用しています。認証や複数画面フローでは、`signIn`、`signOut`、`sendMail`、`verifyEmail` など機能を表すAction名を使用します。

## 4. Form Requestと入力検証

Form Requestは `backend/laravel/app/Http/Requests/` に置き、入力ルール、属性名、エラーメッセージを定義します。現在は次の機能で使用しています。

- 問い合わせ
- ファイル登録・更新
- 投稿登録・更新
- カテゴリ登録・更新
- タグ登録・更新

ログイン、パスワードリセット、新規登録、プロフィール、一般ユーザー管理、認証APIなどでは、Controller内の `$request->validate()` も使用しています。

Form RequestとController内検証の使い分けは、現在統一されていません。本書では現在の配置と責務だけを定義し、具体的な入力ルールと新規実装時の共通方針は、各機能仕様およびバリデーションとセキュリティの仕様で定義します。

Form Requestは入力検証に加えて、リクエスト単位の認可や入力整形も担当できます。ただし、現在の各Form Requestでは `authorize()` はすべて `true` を返し、入力整形Hookも使用していません。現在の認証・認可境界は主にRoute MiddlewareとControllerで管理します。

## 5. ModelとEloquent

Eloquent Modelは `backend/laravel/app/Models/` に置きます。現在のModelでは、必要に応じて次を定義しています。

- Mass Assignment対象の `$fillable`
- JSON変換時に隠す `$hidden`
- 日時やBooleanへ変換する `$casts`
- `belongsTo()`、`belongsToMany()` などのRelation
- 再利用する検索条件のLocal Scope
- UUID主キーなどLaravel標準と異なる主キー設定
- 認証対象Modelの `Authenticatable`
- Notification対象Modelの `Notifiable`

DBのRelation、Index、外部キー、論理削除方式の正本は、Migrationと今後作成するデータベース仕様です。Modelには、現在のSchemaと機能に必要なRelation、Cast、Scopeを定義します。

## 6. ServiceとHelper

現在、ServiceとHelperは主にファイル管理で使用しています。

- `UploadedFileService` は現在、Model経由のサムネイル生成に使用されている
- `UploadedFileService` にはファイル削除用メソッドもあるが、現在のControllerの削除処理では使用されていない
- `UploadedFileHelper` はファイルサイズ表示、画像・プレビュー対象拡張子の判定を扱う

Serviceは外部状態や複数処理を扱う再利用可能な処理、Helperは値の変換や判定などの補助処理に使用しています。ただし、この分離は全機能へ一律に適用されておらず、現在のControllerには同種の処理を直接持つ箇所もあります。

新しいServiceやHelperを追加する場合は、ControllerやModelにすでに同じ責務がないか確認し、機能固有の処理を不必要に共通化しません。

新規機能または既存機能の大きな変更では、複数Modelの操作、Transaction、外部I/O、再利用可能なユースケースをServiceへ分離し、ControllerはHTTP入力、Service呼び出し、Response構築を中心とします。既存Controllerは一括変更せず、テストを整備しながら機能単位で段階的に移行します。

## 7. Middleware

現在はLaravel標準のMiddlewareと独自Middlewareを併用します。

| Middleware | 用途 |
| --- | --- |
| `guest` | 一般ユーザーのパスワードリセット・新規登録フロー |
| `auth` | 一般ユーザーのSession認証 |
| `auth:admin` | 管理者のSession認証 |
| `auth.any` | 一般ユーザーまたは管理者のSession認証 |
| `jwt` | API Access Tokenの検証 |
| `api.guard` | APIのOrigin、Header、任意のDouble Submit Cookie検証 |

独自MiddlewareのAliasは `backend/laravel/bootstrap/app.php` へ登録します。認証条件、JWT、CORS、Cookieの詳細は認証仕様を正本とします。

## 8. NotificationとConsole Command

Notificationは `backend/laravel/app/Notifications/` に置き、現在はMail Channelを使用しています。件名、メールTemplate、Templateへ渡す値をNotificationで構成し、本文は `backend/laravel/resources/views/emails/` のBladeで表示します。

Console Commandは `backend/laravel/app/Console/Commands/` に置きます。現在はRefresh Tokenを削除する `tokens:prune` Commandがあります。定期実行するCommandは、`backend/laravel/bootstrap/app.php` のScheduleへ登録します。

メール固有の送信フローと設定は問い合わせ・メール仕様または認証仕様、Commandの削除条件は認証仕様を正本とします。

## 9. Blade View

Blade Viewは用途に応じて次の場所へ分けます。

| Path | 用途 |
| --- | --- |
| `backend/laravel/resources/views/layouts/` | 画面共通Layout |
| `backend/laravel/resources/views/components/` | 共通表示部品 |
| `backend/laravel/resources/views/pages/` | 画面単位のView |
| `backend/laravel/resources/views/emails/` | メール本文 |

現在の画面共通Layoutは `backend/laravel/resources/views/layouts/default.blade.php` です。Layoutは次のComponentを読み込みます。

- `components.head`
- `components.header`
- `components.footer`
- `components.offcanvas`

`components.navigation` はHeaderから読み込まれます。Page Viewは `layouts.default` を継承し、必要に応じて `meta`、`style`、`script_head`、`content`、`script_body` Sectionを定義します。

POST FormではLaravelのCSRF Tokenを送信するため `@csrf` を使用します。HTML出力とXSS対策の詳細は、バリデーションとセキュリティの仕様で定義します。

## 10. 命名と配置

現在のPHP・Blade・Routeでは、次の命名を基本とします。

| 対象 | 命名 |
| --- | --- |
| PHP Class | PascalCase |
| Controller | PascalCase + `Controller` |
| Form Request | PascalCase + `Request` |
| Notification | PascalCase + `Notification` |
| Service | PascalCase + `Service` |
| Seeder | PascalCase + `Seeder` |
| Method | camelCase |
| Migration filename | Timestamp + snake_case |
| Blade filename | 原則kebab-case |
| URL segment | 原則kebab-case |
| Route Name | dot区切り |

PHP Classのファイル名は、PSR-4 Autoloadで解決できるようClass名と大文字・小文字を含めて一致させます。現在、`SigninController.php` と `SignInController` には不一致があり、実装修正が必要な課題として別途管理します。

コード表記でパスを記載する場合は、先頭に `/` を付けず、リポジトリルート相対で記載します。

## 11. 機能追加・変更時の確認対象

過去の実装順序を固定せず、変更内容に応じて関連する対象を確認します。

| 変更 | 主な確認対象 |
| --- | --- |
| DB変更 | Migration、Model、Relation、Seeder、Test |
| 画面追加・変更 | Route、Middleware、Controller、Blade、Navigation、Test |
| 入力処理 | Form RequestまたはController Validation、認可、CSRF、エラー表示、Test |
| メール | Notification、Email Blade、Mail Config、Mailpitでの確認、Test |
| API | Route、Middleware、Controller、Response、CORS、環境変数、Client、Test |
| 定期処理 | Console Command、Schedule、実行環境、Log、Test |
| ファイル処理 | Request、Controller、Model、Service、Helper、Filesystem Config、Test |

この表は作業順序ではなく、変更漏れを防ぐための確認対象です。変更に関係しないファイルやレイヤーを形式だけで追加しません。

## 12. 現在の制約

- Controller内ValidationとForm Requestが併存している
- ControllerからServiceへ処理を分離しているのは一部機能だけである
- Web RouteはGETとPOSTを使用し、更新・削除・復元もPOSTで実装している
- 数値IDに対する `whereNumber('id')` がすべてのRouteへ統一されていない
- Parameter、Return Value、Propertyの型宣言が統一されていない
- `SigninController.php` のファイル名と `SignInController` のClass名が一致していない
- 主要機能のFeature Testが未整備である
- Laravelを対象とするGitHub Actions Workflowが存在しない
- 複雑度、行数、Coverageなどの数値基準を強制する設定やCIが存在しない

これらは現在実装の事実であり、すべてを直ちに変更する方針を意味しません。実装修正が必要な項目は、影響を確認したうえでGitHub Issueとして管理します。

## 13. 検証

Docker環境起動後、`server/` で実行します。

自動テスト:

```bash
make laravel-test
```

RouteとMiddlewareの確認:

```bash
make laravel-route
```

Laravel Pintは開発依存に含まれていますが、現在はMakefileやComposer Scriptに標準の検証Commandとして定義されていません。コード整形またはStyle検証として標準化する場合は、実行方法とCIを別途整備します。
