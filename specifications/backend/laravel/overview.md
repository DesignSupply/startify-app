---
title: Laravelアプリケーション 概要仕様
status: current
last_updated: 2026-08-13
related_paths:
  - backend/laravel/composer.json
  - backend/laravel/bootstrap/app.php
  - backend/laravel/routes/
  - backend/laravel/app/Http/Controllers/
  - backend/laravel/app/Http/Middleware/
  - backend/laravel/app/Models/
  - backend/laravel/database/migrations/
  - backend/laravel/resources/views/
  - backend/laravel/tests/
  - server/Makefile
  - server/docker/nginx/nginx.conf
---

# Laravelアプリケーション 概要仕様

Startify-AppのLaravelアプリケーションにおける、現在の役割、技術構成、機能境界、認証境界、データ構成、検証状況を定義します。

本書はアプリケーション全体の入口です。アーキテクチャ、画面・機能一覧、データベース、認証、問い合わせとメール、コンテンツ管理、ファイル管理、ユーザー・プロフィール管理、バリデーションとセキュリティの詳細は、各個別仕様で定義します。

## 1. Laravelの役割

`backend/laravel/` は、次の2つのインターフェイスを提供します。

- BladeとWebルートによるMPA
- Next.jsなどの別クライアントから利用する認証API

MPAは画面表示、フォーム処理、セッション認証、DB更新、ファイル保存、メール送信を担当します。APIは現在、一般ユーザー向けの認証機能だけを提供します。投稿などのデータを取得・更新するAPIは実装されていません。

ローカルDocker環境では、Laravel MPAを `https://localhost`、同じアプリケーションのAPIを `https://api.localhost` から提供します。どちらも `backend/_webroot/` をDocument Rootとし、NginxからPHPコンテナーへ処理を渡します。

## 2. 技術構成

主要な実行環境と依存関係は次のとおりです。正確なバージョンは `backend/laravel/composer.json` と `backend/laravel/composer.lock` を正本とします。

| 項目 | 現在の構成 |
| --- | --- |
| PHP | 8.3（ローカルDocker環境）、Composer要件は `^8.2` |
| Laravel | 11.42.1、Composer要件は `^11.31` |
| テンプレート | Blade |
| DB | MariaDB 11.0（ローカルDocker環境） |
| Web認証 | Laravel Session Guard |
| API認証 | `firebase/php-jwt` を使用したRS256 JWTとDB管理のRefresh Token |
| ファイル処理 | Laravel Filesystem、Intervention Image |
| メール | Laravel Notification、Bladeメールテンプレート、Mailpit（ローカル） |
| テスト | PHPUnit 11.5.7、Laravel Test Runner |

`laravel/sanctum` とPersonal Access Token用Migrationは存在しますが、現在の認証APIはSanctumではなく独自のJWT実装を使用します。

## 3. ルーティングとインターフェイス

Laravel 11のルーティング設定は `backend/laravel/bootstrap/app.php` にあり、次のファイルを読み込みます。

| 種別 | 定義ファイル | URLの基点 | 主な用途 |
| --- | --- | --- | --- |
| Web | `backend/laravel/routes/web.php` | `/` | Blade画面、フォーム、セッション認証 |
| API | `backend/laravel/routes/api.php` | `/api` | JWT認証API |
| Console | `backend/laravel/routes/console.php` | なし | Artisan Console Route |
| Health Check | `backend/laravel/bootstrap/app.php` | `/up` | アプリケーションの稼働確認 |

Webルートは文字列形式のController指定を使用します。作成、更新、削除などの変更操作には、現在の実装に合わせて主にPOSTを使用しています。

現在のAPIは次の4エンドポイントです。

| Method | Path | 用途 |
| --- | --- | --- |
| `POST` | `/api/v1/auth/login` | ログインとToken発行 |
| `POST` | `/api/v1/auth/refresh` | Refresh TokenのローテーションとAccess Token再発行 |
| `POST` | `/api/v1/auth/logout` | Refresh Tokenの失効とCookie削除 |
| `GET` | `/api/v1/auth/me` | Access Tokenの検証とユーザー情報取得 |

## 4. MPAの機能領域

現在のWebルートとControllerは、次の機能を提供します。

| 領域 | 主な機能 | アクセス境界 |
| --- | --- | --- |
| 公開画面 | フロントページ | 認証不要 |
| 一般ユーザー認証 | ログイン、ログアウト、パスワードリセット、新規登録 | 機能に応じて公開、`guest`、`auth` |
| 一般ユーザー | ホーム、プロフィール表示・編集 | `auth` Middleware |
| 管理者認証 | ログイン、ログアウト、パスワードリセット | 機能に応じて公開、`auth:admin` |
| 管理者 | ダッシュボード、プロフィール表示・編集 | `auth:admin` Middleware |
| 問い合わせ | 入力、確認、メール送信、完了 | 認証不要 |
| ファイル管理 | 一覧、登録、詳細、編集、削除、ダウンロード | `auth:admin` Middleware |
| 一般ユーザー管理 | 一覧、詳細、編集、論理削除、復元 | `auth:admin` Middleware |
| 投稿閲覧 | 投稿一覧、詳細 | 一般ユーザーまたは管理者Session |
| 投稿管理 | 投稿の作成、編集、論理削除、復元 | `auth:admin` Middleware |
| 分類管理 | カテゴリ・タグの一覧、作成、編集、論理削除、復元 | `auth:admin` Middleware |

一般ユーザーまたは管理者のどちらかを許可する投稿閲覧には、独自の `auth.any` Middlewareを使用します。

## 5. 認証境界

MPAでは、`backend/laravel/config/auth.php` に定義された2つのSession Guardを使用します。

| Guard | Provider | Model | 用途 |
| --- | --- | --- | --- |
| `web` | `users` | `App\Models\User` | 一般ユーザー |
| `admin` | `admin_users` | `App\Models\AdminUser` | 管理者 |

API認証はSession Guardとは独立しています。短寿命のAccess Tokenをレスポンス本文で返し、長寿命のRefresh TokenをCookieで配布して `refresh_tokens` テーブルで管理します。

認証方式、Cookie、CORS、API Request Guard、JWT Claim、Tokenローテーションの詳細は、個別の認証仕様で定義します。

## 6. データ構成

現在のMigrationで管理するテーブルは、次の領域に分かれます。

| 領域 | テーブル |
| --- | --- |
| ユーザー・認証 | `users`、`admin_users`、`password_reset_tokens`、`admin_password_reset_tokens`、`sessions`、`refresh_tokens`、`personal_access_tokens` |
| ファイル | `uploaded_files` |
| 投稿 | `posts`、`categories`、`tags`、`category_post`、`post_tag` |
| Laravel基盤 | `cache`、`cache_locks`、`jobs`、`job_batches`、`failed_jobs` |

投稿と管理者は1対多、投稿とカテゴリ・タグは多対多です。アップロードファイルは、アップロードした管理者を参照します。

一般ユーザー、投稿、カテゴリ、タグの削除状態は、Laravel標準の `SoftDeletes` ではなく `is_deleted` Booleanで管理します。`User`、`Post`、`Category`、`Tag` の各Modelには、`active()` と `onlyDeleted()` Scopeがあります。現在のControllerでは、投稿閲覧時に `active()` を使用し、削除・復元処理などでは `is_deleted` を直接参照・更新します。

## 7. アプリケーション構成と責務

現在の主な責務は次の場所にあります。

| Path | 責務 |
| --- | --- |
| `backend/laravel/app/Http/Controllers/` | Webリクエスト、画面遷移、フォーム処理 |
| `backend/laravel/app/Http/Controllers/Api/` | JSON API |
| `backend/laravel/app/Http/Middleware/` | JWT、API Request Guard、複数Guard認証 |
| `backend/laravel/app/Http/Requests/` | 一部フォームの入力検証 |
| `backend/laravel/app/Models/` | Eloquent Model、Relation、Query Scope |
| `backend/laravel/app/Notifications/` | パスワードリセット、登録、問い合わせメール |
| `backend/laravel/app/Services/` | ファイル操作などの再利用可能な処理 |
| `backend/laravel/resources/views/` | Blade Layout、Page、Component、メールテンプレート |
| `backend/laravel/database/migrations/` | DB Schema |
| `backend/laravel/database/seeders/` | 開発用初期データ |

Controller内で直接入力検証している機能と、Form Requestへ分離している機能が併存しています。現在の使い分けと新規実装時の規約は、バリデーションとセキュリティの個別仕様で定義します。

## 8. 検証の現在状況

Docker環境起動後、Laravelの代表的な検証は `server/` で実行します。

自動テスト:

```bash
make laravel-test
```

登録ルートの確認:

```bash
make laravel-route
```

現在の自動テストは、フロントページのHTTP 200確認とPHPUnitの基本Unit Testだけです。認証、問い合わせ、ファイル管理、ユーザー管理、投稿管理、認証APIの機能テストは存在しません。

`.cursor/rules/dev-backend.mdc` に記載されたコードカバレッジ80%以上などの数値基準は、現在のテスト、設定、CIによって強制されていないため、現在仕様には含めません。

## 9. 現在の制約

- 認証以外のJSON APIは実装されていない
- Laravelを対象とするGitHub Actions Workflowは存在しない
- 主要機能の自動テストが未整備
- Controller内検証とForm Requestが併存している
- 更新・削除のWebルートは、REST標準のMethodへ統一されていない
- Laravel標準の論理削除ではなく、独自の `is_deleted` を使用している

これらは現在実装の事実であり、すべてを直ちに変更する方針を意味しません。実装変更が必要な課題は、仕様書へ未実装の規約として混在させず、影響を確認したうえでGitHub Issueとして管理します。
