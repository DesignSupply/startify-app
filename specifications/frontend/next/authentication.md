---
title: Next.jsアプリケーション 認証仕様
status: current
last_updated: 2026-08-10
related_paths:
  - frontend/next/.env.example
  - frontend/next/src/app/signin/
  - frontend/next/src/app/(auth)/
  - frontend/next/src/components/auth/
  - frontend/next/src/components/dashboard/DashboardContents.tsx
  - frontend/next/src/features/auth/apiAuth.ts
  - frontend/next/src/helpers/api.ts
  - frontend/next/src/helpers/storeAccessToken.ts
  - frontend/next/src/hooks/auth/
  - frontend/next/src/schemas/auth.ts
  - backend/laravel/routes/api.php
  - backend/laravel/.env.example
  - backend/laravel/app/Console/Commands/PruneRefreshTokens.php
  - backend/laravel/app/Http/Controllers/Api/AuthController.php
  - backend/laravel/app/Http/Middleware/JwtAuthenticate.php
  - backend/laravel/app/Http/Middleware/VerifyApiRequestGuard.php
  - backend/laravel/app/Models/RefreshToken.php
  - backend/laravel/app/Providers/AppServiceProvider.php
  - backend/laravel/bootstrap/app.php
  - backend/laravel/config/cors.php
  - backend/laravel/database/migrations/2025_09_21_053231_create_refresh_tokens_table.php
---

# Next.jsアプリケーション 認証仕様

Startify-AppのNext.jsアプリケーションとLaravel API間の認証方式、トークン管理、APIクライアント、認証ルーティング、フォーム、検証方法を定義します。

本認証はStatic ExportされたNext.jsアプリケーションから、別オリジンのLaravel APIへブラウザー上で接続する構成です。Next.jsサーバーでセッションやトークンを保持しません。

## 1. 認証方式

認証にはJWTのSplit Token方式を使用します。

| トークン | 用途 | 保持場所 | 既定の有効期間 |
| --- | --- | --- | --- |
| Access Token | Laravel APIのBearer認証 | JavaScriptモジュールのメモリ | 15分 |
| Refresh Token | Access Tokenの再発行 | Laravel APIが発行するHttpOnly Cookie | 14日（20,160分） |

Access TokenはLaravelがRS256で署名し、ログインまたはリフレッシュ時のJSONレスポンスで返します。Next.js側は `src/helpers/storeAccessToken.ts` のモジュール変数に保存し、`localStorage`、`sessionStorage`、Cookieへ永続化しません。

Refresh Tokenの生値はDBへ保存せず、Laravelの `refresh_tokens` テーブルへSHA-256ハッシュを保存します。リフレッシュ時は旧トークンを失効させ、新しいRefresh Tokenへローテーションします。

Access Tokenはページ再読み込みで失われます。認証状態を復元するときは、Refresh Cookieを使って新しいAccess Tokenを取得します。

> **認証基盤の将来の差し替えについて**
>
> 現在の認証基盤はLaravelです。Next.jsはLaravelの内部実装ではなく、本仕様で定義する認証API契約に依存します。将来、Cloudflare Workersなど別の認証基盤へ移行する場合も、エンドポイント、レスポンス形式、Cookie、Bearer Token、エラー応答の契約を維持することで、API Base URLの変更を中心に切り替えられる構成を目指します。Cloudflare Accessのように認証方式や責務が異なるサービスを利用する場合は、Laravel認証との責務分担を別途設計します。この方針は将来案であり、現在は実装されていません。

## 2. APIエンドポイント

Base URLは `NEXT_PUBLIC_API_BASE_URL` で指定し、現在のAPIプレフィックスは `/api/v1` です。

| エンドポイント | メソッド | Laravelミドルウェア | `credentials: 'include'` | 主なレスポンス |
| --- | --- | --- | --- | --- |
| `/auth/login` | POST | `throttle:login`、`api.guard` | 必須 | Access Tokenを返し、Refresh Cookieを発行 |
| `/auth/refresh` | POST | `throttle:refresh`、`api.guard` | 必須 | Refresh Tokenをローテーションし、Access Tokenを返す |
| `/auth/logout` | POST | `api.guard` | 必須 | Refresh Tokenを失効し、Cookieを削除。HTTP 204 |
| `/auth/me` | GET | `jwt` | 不要 | Bearer Tokenを検証し、ユーザー情報を返す |

正常時のJSONレスポンス契約は次のとおりです。`logout` は本文を返しません。

| 対象 | HTTPステータス | レスポンス |
| --- | --- | --- |
| `login`、`refresh` | 200 | `{ "access_token": string }` |
| `me` | 200 | `{ "id": number, "name": string, "email": string, "created_at"?: string }` |
| `logout` | 204 | レスポンス本文なし |

Laravelが明示的に返す認証エラーは、原則として `{ "code": string, "message": string }` のJSON形式です。未認証は401、Origin・必須Header・任意のRefresh CSRF検証失敗は403、ユーザーが存在しない場合は404、Rate Limit超過は429、入力検証失敗はLaravel標準の422となります。サーバー設定不備などでは500を返す場合があります。別の認証基盤へ差し替える場合は、これらのレスポンス形式とHTTPステータスも互換対象として扱います。

`login` と `refresh` は1分あたり5リクエストに制限します。ログインはメールアドレスとIP、リフレッシュはIPをRate Limitのキーとして使用します。

## 3. CookieとJWT

Refresh Cookieの現在の属性は次のとおりです。

| 属性 | 値 |
| --- | --- |
| Cookie名 | `refresh_token` |
| `HttpOnly` | `true` |
| `Secure` | `true` |
| `SameSite` | `None` |
| `Path` | `/api/v1/auth` |
| `Domain` | `REFRESH_COOKIE_DOMAIN`。未指定時はリクエストホスト |

Cookieの発行、ローテーション、削除には同じPath、Domain、Secure、SameSite属性を使用します。`Secure` Cookieを使用するため、ローカルAPIも `https://api.localhost` で接続します。

Access Tokenには現在、`iss`、`sub`、`iat`、`nbf`、`exp`、`jti` を含めます。`JwtAuthenticate` はRS256の公開鍵検証を行い、`sub` をLaravel Requestへ渡します。

## 4. Next.js APIクライアント

`src/helpers/api.ts` の `apiFetch()` が、認証APIを含むHTTP通信を共通化します。

- `NEXT_PUBLIC_API_BASE_URL` を相対APIパスへ付与する
- `Accept: application/json`、`Content-Type: application/json` を付与する
- `X-Requested-With: XMLHttpRequest` を常時付与する
- `auth: true` の場合だけAccess TokenをBearer Headerへ付与する
- `withCredentials: true` の場合だけ `credentials: 'include'` を使用する
- JSONレスポンスを解析し、非成功レスポンスを `status`、`code`、`data` 付きのErrorとして返す
- `autoRefresh: true` のリクエストが401の場合、リフレッシュ後に1回だけ再試行する

同時に複数のリクエストが401になった場合は、モジュール内の共有PromiseによってRefreshリクエストを1回に集約します。ログイン、リフレッシュ、ログアウト自体には自動リフレッシュを適用しません。

認証API固有の呼び出しは `src/features/auth/apiAuth.ts` に置きます。

| 関数 | `apiFetch`設定 | トークン処理 |
| --- | --- | --- |
| `login()` | POST、`withCredentials: true` | 応答のAccess Tokenをメモリへ保存 |
| `refresh()` | POST、`withCredentials: true` | 応答のAccess Tokenをメモリへ保存 |
| `logout()` | POST、`withCredentials: true` | 成功後にAccess Tokenを削除 |
| `me()` | GET、`auth: true`、`autoRefresh: true` | 401時にRefresh Cookieから認証復元を試行 |

## 5. TanStack Queryによる認証状態

認証済みユーザー情報は `src/hooks/auth/useAuth.ts` でTanStack Queryにより管理します。Query Keyは `['auth', 'me']` です。

- `useMeQuery()` は `/auth/me` から現在ユーザーを取得する
- 401エラーは再試行しない
- その他の失敗は最大2回の再試行に制限する
- `useLoginMutation()` は成功後に `['auth', 'me']` をinvalidateする
- `useLogoutMutation()` は成功後にAccess Tokenを削除し、同じQueryをinvalidateする

Access TokenそのものはTanStack Queryのキャッシュへ保存しません。TanStack Queryはユーザー情報とAPI状態、`storeAccessToken.ts` はBearer Tokenのメモリ保持を担当します。

## 6. 認証ルーティング

認証保護対象は `src/app/(auth)/` のRoute Groupへ配置します。現在の対象は次のとおりです。

- `/dashboard/`
- `/posts/`
- `/posts/[id]/`

`src/app/(auth)/layout.tsx` はServer Componentのレイアウトで、Client Componentの `AuthGuard` に子要素を渡します。

`AuthGuard` は `useMeQuery()` の結果を確認し、ユーザー情報を取得できない場合は `/signin` へ `router.replace()` します。現在は401の認証エラーと通信・サーバーエラーを区別していないため、いずれのエラーでもユーザー情報がなければサインイン画面へ遷移します。判定中またはユーザー情報を取得できない状態では子要素を描画しません。

サインインページでは、すでにユーザー情報を取得できている場合に `/dashboard` へリダイレクトします。ログアウト成功後は `/signin` へリダイレクトします。

## 7. サインインフォーム

`src/components/auth/SigninForm.tsx` はReact Hook FormとZodを使用します。スキーマは `src/schemas/auth.ts` で定義します。

| フィールド | 現在の検証 |
| --- | --- |
| メールアドレス | 必須、メールアドレス形式 |
| パスワード | 必須、8文字以上 |

送信中はログインボタンを無効化します。成功時は `next` クエリの値、未指定時は `/dashboard` へ遷移します。APIエラー時はLaravelレスポンスのメッセージを表示し、取得できない場合は共通メッセージを表示します。

## 8. Laravel側のセキュリティ

### リクエストガード

`VerifyApiRequestGuard` はログイン、リフレッシュ、ログアウトに適用し、`X-Requested-With: XMLHttpRequest` を必須とします。

`ALLOWED_ORIGINS` が設定されている場合は、Originを完全一致で検証します。Originがない場合のみ、Refererから `scheme://host[:port]` を再構成して検証します。

### CORS

`config/cors.php` は `api/*` にCORSを適用します。

- 許可Originは `CORS_ALLOWED_ORIGINS` のカンマ区切りリスト
- `supports_credentials: true`
- `Content-Type`、`X-Requested-With`、`Authorization`、`Accept`、`Origin` を許可
- Cookieを使用するため、許可Originにワイルドカードを使用しない

### 任意のDouble Submit Cookie

Laravelには `ENABLE_REFRESH_CSRF` を有効にした場合のDouble Submit Cookie処理があります。`refresh_csrf` Cookieと `X-CSRF-Token` Headerの一致を、リフレッシュとログアウトで検証します。

現在のNext.js APIクライアントは `X-CSRF-Token` を送信しないため、この機能を有効にするとリフレッシュとログアウトが403になります。現行構成では `ENABLE_REFRESH_CSRF=false` を互換条件とします。有効化する場合は、Next.jsがCSRF Tokenを取得できるCookie DomainとPath、または明示的なToken受け渡し方式を設計し、`X-CSRF-Token` の送信とCORSの `allowed_headers` への追加を同時に実装してください。

## 9. 環境変数

### Next.js

| 変数 | 用途 |
| --- | --- |
| `NEXT_PUBLIC_API_BASE_URL` | `/api/v1` を含むLaravel APIのBase URL |

この値はブラウザーへ公開され、Static Exportのビルド時に固定されます。秘密情報を含めないでください。

### Laravel

| 変数 | 用途 | 既定値・現在の注意 |
| --- | --- | --- |
| `JWT_PRIVATE_KEY_PATH` | Access Token署名用の秘密鍵 | 読み取り不能時は500 |
| `JWT_PUBLIC_KEY_PATH` | Access Token検証用の公開鍵 | 読み取り不能時は500 |
| `JWT_ACCESS_TTL` | Access Tokenの有効期間（分） | 15分 |
| `JWT_REFRESH_TTL` | Refresh TokenとCookieの有効期間（分） | 20,160分 |
| `REFRESH_COOKIE_DOMAIN` | Refresh CookieのDomain | 未指定時はリクエストホスト |
| `CORS_ALLOWED_ORIGINS` | CORSで許可するOrigin | カンマ区切り |
| `ALLOWED_ORIGINS` | API Guardで許可するOrigin | 未設定時はOrigin検証を省略 |
| `ENABLE_REFRESH_CSRF` | Double Submit Cookieを有効化 | 既定は無効。現Next.js実装は未対応 |
| `REFRESH_TOKENS_RETAIN_DAYS` | 失効・期限切れTokenの保持日数 | 30日 |

`ALLOWED_ORIGINS` と `ENABLE_REFRESH_CSRF` は現在の `backend/laravel/.env.example` に未記載です。設定を追加する場合は、上記の挙動とNext.js側の互換性を確認してください。

## 10. Refresh Tokenの保守

Laravelの `tokens:prune` コマンドは、失効済みのRefresh Tokenと、期限切れから保持期間を過ぎたRefresh Tokenを削除します。失効済みTokenは保持期間に関係なく削除対象です。

- 期限切れTokenの既定の保持日数は30日
- `--dry-run` で対象件数のみ確認可能
- `--limit` で1回の削除件数を指定可能
- Laravel Schedulerへ毎日00:00の実行として登録されている

## 11. エラーと現在の制約

- 401発生時の自動リフレッシュは1回だけ行う
- リフレッシュに失敗した場合はAccess Tokenを削除する
- `AuthGuard` は認証判定中にフォールバックUIを表示せず `null` を返す
- `AuthGuard` は401の認証エラーと通信・サーバーエラーを区別せず、ユーザー情報を取得できなければ `/signin` へ遷移する
- `/auth/login`、`/auth/refresh` のRate Limit超過はLaravel標準の429応答となる
- APIレスポンスはTypeScript型として扱うが、認証レスポンスのZod実行時検証は現在行っていない
- `SigninForm` は `next` クエリを遷移先として受け取るが、`AuthGuard` は現在 `next` を付与していない
- `SigninForm` は `next` クエリを内部パスとして検証せず `router.replace()` へ渡している。信頼できない遷移先を受け入れる可能性があるため、アプリケーション内の許可された相対パスだけを受け入れる検証が必要
- Static Exportのため、ページ配信前のサーバーサイド認可は行わない。保護対象データは必ずLaravel APIでも認証・認可する

## 12. 検証

Next.jsの認証テストは `frontend/next/` で実行します。

```bash
npm run test:auth
```

lint、型チェック、全テストを含む確認:

```bash
npm run check
```

現在の自動テストは次を対象とします。

- `useMeQuery()` のユーザー情報取得
- ログイン成功後のQuery invalidate
- ログアウト成功後のToken削除とQuery invalidate
- サインインフォームの入力検証
- ログイン成功後の `/dashboard` 遷移

現在、`apiFetch()` の401自動リフレッシュ、single-flight、`AuthGuard`、Laravel認証APIは自動テストの対象外です。変更時は必要に応じてテスト追加またはブラウザーでの結合確認を行います。

代表的な結合確認フローは次のとおりです。

```text
login → me → Access Token失効 → refresh → me → logout → me（401）
```

## 13. 移行元資料

本書は、次の既存資料から設計意図を抽出し、現在のコード、設定、テストと照合して再構成しています。

- `specifications/backend/laravel/TASK_014.md`
- `.cursor/rules/dev-backend.mdc`

これらはドキュメント移行が完了するまで設計意図の確認に使用しますが、現在仕様としては本書と現在の実装を優先します。
