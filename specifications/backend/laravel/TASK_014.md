---
title: バックエンド実装タスクリスト（Laravel）:ログイン認証APIの実装
id: laravel_task_014
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:ログイン認証APIの実装

JWTを用いてフロント側からAPI経由で一般ユーザーのログイン認証できる機能を実装します。

---

## 1. 依存モジュールのインストールとキーペア生成

### 1.1. firebase/php-jwt のインストール

JWT認証に必要となるモジュールをインストールします。

```
cd ./server
docker compose exec app bash -lc "cd /var/www/html/laravel && composer require firebase/php-jwt:^6 --no-interaction"
```

### 1.2. プロジェクト内にキーペア生成

RSA鍵（4096）を生成し `/backend/laravel/storage/keys` に格納します。署名についてはRS256とします。（iss/aud/nbf/iat/exp/jtiを付与）

```
docker compose exec app bash -lc "mkdir -p /var/www/html/laravel/storage/keys && \
  openssl genrsa -out /var/www/html/laravel/storage/keys/jwtRS256.key 4096 && \
  openssl rsa -in /var/www/html/laravel/storage/keys/jwtRS256.key -pubout -out /var/www/html/laravel/storage/keys/jwtRS256.key.pub"
```

そして `/backend/laravel/.env` にキーの指定を追記します。

```.env
JWT_PRIVATE_KEY_PATH=/var/www/html/laravel/storage/keys/jwtRS256.key
JWT_PUBLIC_KEY_PATH=/var/www/html/laravel/storage/keys/jwtRS256.key.pub
JWT_ACCESS_TTL=15
JWT_REFRESH_TTL=20160
```

**`/backend/laravel/storage/keys` はGit除外・権限0600、　`/backend/laravel/.env`　でパス管理するものとする**

---

## 2. ログイン認証APIのマイグレーションファイルを作成

ログイン認証APIに関連するマイグレーションファイルを作成、マイグレーションを実行しテーブルを更新します。

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_refresh_tokens_table.php`
  - テーブル: `refresh_tokens`
  - カラム
    - `id` / uuid / primary key
    - `user_id` / big integer / unsigned
    - `token_hash` / char(64) または、binary(32) / unique / ※生値は保存しない（ハッシュ化）
    - `ip` / varchar(45) / ※IPv6対応
    - `ua` / text
    - `revoked_at` / timestamp / nullable
    - `expires_at` / timestamp / nullable
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable
  - 外部キー制約
    - `user_id` → users.id / on delete cascade
  - インデックス制約
    - `user_id`
    - `expires_at`
    - `revoked_at`
    - `token_hash` / unique

**`token_hash` はハッシュ化された値を保存**

---

## 3. ログイン認証APIのモデルを作成

ログイン認証APIのモデルを作成します。

- モデル
  - モデル: `RefreshToken`
  - パス: `/backend/laravel/app/Models/RefreshToken.php`
  - クラス: `RefreshToken`
  - 機能仕様
    - データベースとの関連処理（リレーション）

---

## 4. ログイン認証APIのルーティングを作成

ログイン認証APIで使用するルーティングを作成します。今回はAPI専用のルーティングとして扱うものとし、`/backend/laravel/routes/api.php` に定義することで、MPAのルーティングとは分離させます。

- ルーティング
  - ログイン（フォーム送信）
    - パス: `/api/v1/auth/login`
    - メソッド: `POST`
    - ルート名: `api.v1.auth.login`
  - リフレッシュトークン再発行
    - パス: `/api/v1/auth/refresh`
    - メソッド: `POST`
    - ルート名: `api.v1.auth.refresh`
  - ログアウト
    - パス: `/api/v1/auth/logout`
    - メソッド: `POST`
    - ルート名: `api.v1.auth.logout`
  - JWT検証
    - パス: `/api/v1/auth/me`
    - メソッド: `GET`
    - ルート名: `api.v1.auth.me`

**`api.v1.auth.me` のルートはjwtミドルウェア適用**

---

## 5. ログイン認証APIのミドルウェアを作成

ログイン認証APIで使用するミドルウェアを作成します。

- ミドルウェア
  - クラス: `JwtAuthenticate`
  - パス: `/backend/laravel/app/Http/Middleware/JwtAuthenticate.php`
  - 機能仕様
    - Authorization Bearer検証（RS256/exp/nbf/jti）

作成したミドルウェアは `/backend/laravel/app/Http/Kernel.php` で登録する。

**リバースプロキシ環境ではHTTPS判定崩れ防止のためTrustProxies有効とする**

---

## 6. ログイン認証APIのコントローラーを作成

ログイン認証APIのコントローラーを作成します。

- コントローラー
  - クラス: `AuthController`
  - パス: `/backend/laravel/app/Http/Controllers/Api/AuthController.php`
  - メソッド:
    - `login`
    - `refresh`
    - `logout`
    - `me`

**`login` では認証後アクセストークンとリフレッシュトークンを発行し、リフレッシュトークンはクッキー（ `HttpOnly + Secure + SameSite(None)` ）で送信、アクセストークンはJSONレスポンスで返し、フロント側からは `Authorization: Bearer` で送信**
**`refresh` ではリフレッシュトークンが失効後に新しいリフレッシュトークンを発行する**
**`logout` ではリフレッシュトークンを破棄し、クッキーから削除する**
**`me` ではJWT検証とユーザー情報を返す**

---

## 7. CORS、クッキーの設定、レート制限

- `api.v1.auth.refresh` および `api.v1.auth.logout` のルートは　`credentials: include` 必須（HttpOnly Refresh Cookie送信）とする。
- 許可するオリジンは開発環境下では `http://localhost:3000` 、本番環境では `https://example.com` の想定とし、`/backend/laravel/.env` で管理する。
- `Allow-Credentials: true`（クライアント側は `refresh/logout` 呼び出し時のみ `credentials: 'include'`）とする。
- Refreshのクッキー属性は ` HttpOnly; Secure; SameSite=None; Path=/api/v1/auth;` とし、 `Domain` については開発環境では `api.localhost` 、本番環境では `api.example.com` を想定し `/backend/laravel/.env` で管理する。
- RefreshのSet-Cookieは `Path=/api/v1/auth` とする。
- 削除用のSet-Cookieも、発行時と同一の `Path` / `Domain` / `SameSite` を使用すること。
- レート制限について `login/refresh` に5req / 分のスロットリングを設定する。
- `Authorization` 、 `Content-Type` などを許可ヘッダーとする。
- 検証失敗、期限切れの場合には401エラーとする。
- ユーザーが該当しない、無効化されている場合には404エラーとする。

### 7.1 CORS 実装手順（Cookie送信対応）

- 目的
  - ブラウザから `credentials: include` でクッキー送信を許可するため、オリジンを特定し `Allow-Credentials: true` を返す。
- 追加ファイル
  - パス: `/backend/laravel/config/cors.php`
  - 設定方針:
    - `paths`: `['api/*']`
    - `allowed_origins`: ENV `CORS_ALLOWED_ORIGINS`（カンマ区切り）を配列化して採用（例: `http://localhost:3000`）
    - `allowed_methods`: `GET, POST, PUT, PATCH, DELETE, OPTIONS`
    - `allowed_headers`: `Content-Type, X-Requested-With, Authorization, Accept, Origin`
    - `supports_credentials`: `true`
- ENV
  - パス: `/backend/laravel/.env`
  - 追記:
    - `CORS_ALLOWED_ORIGINS=http://localhost:3000`
  - 本番例:
    - `CORS_ALLOWED_ORIGINS=https://example.com`
- 注意事項
  - `credentials: include` を使う場合、`Access-Control-Allow-Origin: *` は不可。必ず特定オリジンを返すこと。
  - レスポンスは `Vary: Origin` を含む（ブラウザキャッシュ考慮）。
- 反映/確認
  1) 設定キャッシュクリア: `docker compose exec app bash -lc "cd /var/www/html/laravel && php artisan optimize:clear"`
  2) フロント（`http://localhost:3000`）から `credentials: 'include'` で `https://api.localhost` にアクセスし、レスポンスヘッダーに以下が含まれることを確認
     - `Access-Control-Allow-Origin: http://localhost:3000`
     - `Access-Control-Allow-Credentials: true`
  3) Cookieが送受信されること（`refresh_token` の送信/削除）

---

## 8. リフレッシュトークンのクリーンアップ処理を作成

コマンドを作成し、スケジューラーによるリフレッシュトークンのクリーンアップ処理を作成します。

- コマンド
  - パス: `/backend/laravel/app/Console/Commands/PruneRefreshTokens.php`
  - クラス: `PruneRefreshTokens`
  - シグネチャ: `tokens:prune`
- 機能仕様
  - `refresh_tokens` テーブルから以下の行を削除する
    - `revoked_at IS NOT NULL`（失効済み）
    - `expires_at < NOW() - 保持日数`（期限切れ＋保持期間経過）
  - 保持日数は環境変数で指定（デフォルト: 30日）
  - Laravelのスケジューラーを使って日次で実行される（毎日0:00に実行）
- 環境変数
  - パス: `/backend/laravel/.env`
  - 追記: `REFRESH_TOKENS_RETAIN_DAYS=30`

作成したコマンドは `/backend/laravel/bootstrap/app.php` でスケジュール処理を設定する

---

## 9. CSRF対策用のミドルウェア追加

APIへのリクエストに対して検証するためのミドルウェアを作成します。

- ミドルウェア
  - クラス: `VerifyApiRequestGuard`
  - パス: `/backend/laravel/app/Http/Middleware/VerifyApiRequestGuard.php`
  - 機能仕様
    - Origin/Referer許可: ヘッダーOriginもしくはRefererのホストがALLOWED_ORIGINSに含まれること
    - X-Requested-Withの検証: XMLHttpRequestを必須に
    - Double Submit Cookie（refresh/logout時のみ）
      - Cookie: refresh_csrf（HttpOnly=false, Secure, SameSite=None, Path=/api/v1/auth）
      - ヘッダー: X-CSRF-Tokenと一致検証（hash_equals）
    - refresh_csrf Cookieの属性でSecureを必須とする
    - Origin/Referer検証でOriginヘッダーを厳密照合（完全一致、ポート含む）。Originがない場合のみRefererから「scheme://host[:port]」を再構成して照合。
  - APIのルーティングで `login` 、`refresh` 、`logout` は `VerifyApiRequestGuard` のミドルウェアを通すようにする

---

## 10. ログイン認証APIのテスト

---
