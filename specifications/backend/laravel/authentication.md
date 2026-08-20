---
title: Laravelアプリケーション 認証仕様
status: current
last_updated: 2026-08-14
related_paths:
  - backend/laravel/.env.example
  - backend/laravel/app/Console/Commands/PruneRefreshTokens.php
  - backend/laravel/app/Http/Controllers/AdminController.php
  - backend/laravel/app/Http/Controllers/AdminPasswordForgotController.php
  - backend/laravel/app/Http/Controllers/AdminPasswordResetController.php
  - backend/laravel/app/Http/Controllers/Api/AuthController.php
  - backend/laravel/app/Http/Controllers/PasswordForgotController.php
  - backend/laravel/app/Http/Controllers/PasswordResetController.php
  - backend/laravel/app/Http/Controllers/SigninController.php
  - backend/laravel/app/Http/Controllers/SignOutController.php
  - backend/laravel/app/Http/Controllers/SignUpController.php
  - backend/laravel/app/Http/Middleware/AuthenticateAny.php
  - backend/laravel/app/Http/Middleware/JwtAuthenticate.php
  - backend/laravel/app/Http/Middleware/VerifyApiRequestGuard.php
  - backend/laravel/app/Models/AdminUser.php
  - backend/laravel/app/Models/RefreshToken.php
  - backend/laravel/app/Models/User.php
  - backend/laravel/app/Notifications/
  - backend/laravel/app/Providers/AppServiceProvider.php
  - backend/laravel/bootstrap/app.php
  - backend/laravel/config/auth.php
  - backend/laravel/config/cors.php
  - backend/laravel/config/session.php
  - backend/laravel/routes/api.php
  - backend/laravel/routes/web.php
  - backend/laravel/database/migrations/
  - frontend/next/src/features/auth/apiAuth.ts
  - frontend/next/src/helpers/api.ts
  - frontend/next/src/helpers/storeAccessToken.ts
---

# Laravelアプリケーション 認証仕様

Startify-AppのLaravelアプリケーションが提供する、一般ユーザーと管理者のSession認証、新規ユーザー登録、パスワード再設定、Next.js向けJWT認証APIを定義します。

本書はLaravel側の認証処理を正本とします。画面とRouteの索引は `specifications/backend/laravel/screens-and-features.md`、認証テーブルのSchemaは `specifications/backend/laravel/database.md`、プロフィールと管理者による一般ユーザー管理は `specifications/backend/laravel/user-and-profile-management.md`、Next.js側のToken保持と画面制御は `specifications/frontend/next/authentication.md` を参照してください。

## 1. 認証方式と対象

現在は、同じ一般ユーザー情報に対して2種類の認証方式を提供します。管理者のAPI認証は実装していません。

| 対象 | Interface | 認証方式 | 認証情報 |
| --- | --- | --- | --- |
| 一般ユーザー | Laravel MPA | `web` Session Guard | `users`テーブル |
| 管理者 | Laravel MPA | `admin` Session Guard | `admin_users`テーブル |
| 一般ユーザー | JSON API | RS256 Access Token + DB管理Refresh Token | `users`、`refresh_tokens`テーブル |

MPAのSession認証とAPIのJWT認証は同じ`users`テーブルを参照しますが、認証処理と認証状態は独立しています。MPAへログインしてもAPI Tokenは発行されず、APIへログインしてもLaravelのSession認証状態にはなりません。

`laravel/sanctum`、Sanctum設定、`personal_access_tokens` Migrationは存在しますが、現在の認証APIでは使用していません。

## 2. Session Guardとアクセス境界

`backend/laravel/config/auth.php` の既定Guardは `web`、既定Password Brokerは `users` です。

| Guard | Driver | Provider | Model | 主なMiddleware |
| --- | --- | --- | --- | --- |
| `web` | `session` | `users` | `App\Models\User` | `auth`、`guest` |
| `admin` | `session` | `admin_users` | `App\Models\AdminUser` | `auth:admin` |

独自の `auth.any` Middlewareは `web` または `admin` のいずれかが認証済みであればリクエストを許可し、どちらも未認証の場合は一般ユーザーの `/signin` へRedirectします。

未認証時のRedirect先は、Request Pathが `admin*` なら `/admin`、それ以外は `/signin` です。一般ユーザーのパスワード再設定と新規登録は `guest` の内側にあります。一般・管理者ログインと管理者パスワード再設定には、現在 `guest` Middlewareを指定していません。

Session Driverは環境変数で決まり、`backend/laravel/.env.example` はDatabase Session、保存期間120分、Session暗号化無効を指定しています。一般ユーザーと管理者は同じSession Storeを使います。

## 3. MPAログインとログアウト

### 3.1 一般ユーザー

`POST /signin/auth` はRequestから `email` と `password` を取得し、次の順に確認します。

1. メールアドレスに一致するユーザーが存在する
2. `is_deleted` が `false` である
3. `Auth::attempt()` でパスワードが一致する
4. 認証成功後にSession IDを再生成する
5. `/home` へRedirectする

現在、ログイン処理には明示的な入力Validationがなく、ユーザーの不存在、削除状態、パスワード不一致を異なるエラーとして返します。`email_verified_at` はログイン条件に使用していません。

`POST /signout` は `web` GuardからLogoutし、CSRF Tokenを再生成して `/signin` へRedirectします。現在はSession全体のInvalidateは行いません。

### 3.2 管理者

`POST /admin/signin` はメールアドレスに一致する管理者を取得し、`Auth::guard('admin')->attempt()` でパスワードを確認します。成功後はSession IDを再生成し、`/admin/dashboard` へRedirectします。

管理者ログインにも明示的な入力Validationはなく、管理者の不存在とパスワード不一致を異なるエラーとして返します。`email_verified_at` はログイン条件に使用していません。

`POST /admin/signout` は `admin` GuardからLogoutし、CSRF Tokenを再生成して `/admin` へRedirectします。現在はSession全体のInvalidateは行いません。

## 4. 新規ユーザー登録

新規登録は `guest` Middleware内で、同一ブラウザーのLaravel Sessionを使った複数画面フローとして実装しています。

```text
メールアドレス入力 → 確認メール送信 → メール内URLへアクセス → 登録情報入力 → 登録完了
```

### 4.1 確認メール送信

`POST /signup/request` はメールアドレスの必須、形式、`users.email` のUniqueを検証します。60文字のランダムTokenを生成し、未保存の `User` Instanceを通知先として確認メールを送信します。

送信後は、次の値をSessionへ保存します。確認Tokenを保存する専用テーブルはありません。

| Session Key | 内容 |
| --- | --- |
| `signup_email` | 登録予定のメールアドレス |
| `signup_token` | 確認メールへ埋め込んだToken |

確認メールのURLは `/signup/verify/{token}?email=...` です。別ブラウザー、別Device、またはSession消失後に同じURLを使用することは前提としておらず、その場合は確認メールを再送してフローを開始し直します。

### 4.2 Token確認と登録

`GET /signup/verify/{token}` は、URLのTokenとメールアドレスをSessionの `signup_token`、`signup_email` と比較し、一致すれば `/signup/register` へRedirectします。

登録画面と `POST /signup/register/store` は現在 `signup_email` の存在だけを確認し、Token照合済みであることを示す状態を確認しません。そのため、確認メール送信後の同じSessionで登録画面へ直接アクセスすると、メール内Tokenの照合を経ずに登録できます。

登録時は名前の必須・文字列・255文字以内と、パスワードの必須・文字列・8文字以上・確認入力一致を検証します。Sessionのメールアドレスを使って `users` を作成します。Controllerは `email_verified_at` へ登録時刻を渡しますが、現在は `User::$fillable` に含まれないためMass Assignmentの対象にならず、保存されません。登録後は `signup_email` と `signup_token` をSessionから削除します。

## 5. パスワード再設定

一般ユーザーと管理者はLaravel Password Brokerを使用し、別々のProviderとToken Tableを参照します。

| 対象 | Broker | Token Table | 有効期間 | 設定上の再発行Throttle |
| --- | --- | --- | --- | --- |
| 一般ユーザー | `users` | `password_reset_tokens` | 60分 | 60秒 |
| 管理者 | `admins` | `admin_password_reset_tokens` | 60分 | 60秒 |

### 5.1 再設定メール

一般ユーザーはメールアドレスの必須・形式を検証し、登録済みかつ `is_deleted=false` のユーザーだけに通知します。管理者はメールアドレスの必須・形式を検証し、登録済みの管理者だけに通知します。

Password Brokerが生成したTokenのHashをDBへ保存し、生のTokenとメールアドレスを通知URLへ含めます。一般ユーザーは `/password-reset/{token}`、管理者は `/admin/password-reset/{token}` を使用します。

ControllerはBrokerの `sendResetLink()` ではなく `createToken()` を直接呼び出します。この呼び出しでは再発行Throttleの判定を行わず、既存Tokenを削除して新しいTokenへ置き換えます。そのため、表の60秒は現在の設定値ですが、メール送信処理には実質適用されていません。

現在は一般・管理者とも、メール送信成功ログへメールアドレスと生のTokenを出力します。

### 5.2 パスワード更新

再設定処理はToken、メールアドレス、新しいパスワードを受け取り、必須、メール形式、8文字以上、確認入力一致を検証します。対象ユーザーの存在とPassword BrokerによるToken照合を確認した後、パスワードをHash化して保存し、使用済みTokenを削除します。

一般ユーザーは削除済みアカウントを拒否します。更新後は一般ユーザーを `/signin`、管理者を `/admin` へRedirectします。現在、パスワード変更に伴う既存SessionまたはRefresh Tokenの一括失効は行いません。

`PasswordResetToken` と `AdminPasswordResetToken` Modelは現在のPassword Broker処理から使用されていません。

## 6. JWT認証API

一般ユーザー向けAPIは `/api/v1/auth` 配下に4つのEndpointを提供します。

| Endpoint | Middleware | 入力・認証 | 正常時の処理 |
| --- | --- | --- | --- |
| `POST /login` | `throttle:login`、`api.guard` | Email、Password | Access Token返却、Refresh Cookie発行 |
| `POST /refresh` | `throttle:refresh`、`api.guard` | Refresh Cookie | 旧Token失効、新TokenとAccess Token発行 |
| `POST /logout` | `api.guard` | Refresh Cookie | 対応Token失効、Cookie削除、204 |
| `GET /me` | `jwt` | Bearer Access Token | 一般ユーザー情報返却 |

ログインはメールアドレスの必須・形式とパスワードの必須・文字列を検証し、認証失敗を `invalid_credentials` の401へ統一します。現在、`is_deleted` と `email_verified_at` はAPIログイン条件に使用していません。

`me` はJWTの `sub` からユーザーを取得し、ID、名前、メールアドレス、作成日時を返します。現在、ここでも `is_deleted` は確認しません。

ログインはメールアドレスとIPの組み合わせ、RefreshはIP単位で、それぞれ1分あたり5回に制限します。

### 6.1 正常レスポンス

| Endpoint | HTTP Status | Response Body |
| --- | --- | --- |
| `POST /login` | 200 | `{ "access_token": string }` |
| `POST /refresh` | 200 | `{ "access_token": string }` |
| `POST /logout` | 204 | なし |
| `GET /me` | 200 | `{ "id": number, "name": string, "email": string, "created_at": string|null }` |

ログインとRefreshでは、Response Bodyに加えてRefresh Cookieを発行します。LogoutはRefresh Cookieを削除します。

### 6.2 エラーレスポンス

Controllerと独自Middlewareが明示的に返す認証エラーは、原則として `{ "code": string, "message": string }` です。

| HTTP Status | 主なCode・発生条件 |
| --- | --- |
| 401 | `invalid_credentials`、`refresh_missing`、`refresh_invalid`、`token_missing`、`token_invalid` |
| 403 | `forbidden_origin`、`forbidden_request`、`forbidden_csrf` |
| 404 | `user_not_found` |
| 422 | Login入力Validation失敗。Laravel標準Validation Error形式 |
| 429 | LoginまたはRefreshのRate Limit超過。Laravel標準形式 |
| 500 | JWT鍵の読込失敗などのServer設定不備 |

期限切れAccess Tokenは現在 `token_invalid` の401として扱います。JWT秘密鍵の読込失敗はControllerが500を中断Responseとして返し、公開鍵の読込失敗は `server_misconfig` の500を返すため、すべての500 Responseが同じJSON契約になるわけではありません。

## 7. Access Token

Access Tokenは `firebase/php-jwt` を使い、LaravelがRS256の秘密鍵で署名します。既定の有効期間は15分です。

| Claim | 現在の値 |
| --- | --- |
| `iss` | LaravelのBase URL |
| `sub` | 一般ユーザーIDの文字列 |
| `iat` | 発行時刻 |
| `nbf` | 発行時刻 |
| `exp` | 発行時刻 + 有効期間 |
| `jti` | Ordered UUID |

`JwtAuthenticate` はAuthorization Headerの `Bearer` Tokenを公開鍵で検証し、`nbf`、`exp`、`sub` を確認します。`firebase/php-jwt` の `decode()` が現在の既定値で `nbf` と `exp` を検証した後、Middlewareでも60秒の差を含む時刻条件を確認します。ただし、Library側の検証が先に行われるため、この追加条件はTokenの利用可能時間を前後60秒へ拡張しません。検証済みの `sub` とPayloadをRequest Attributeへ渡します。

期限切れを含むJWTのDecode失敗は現在共通のCatch処理に入り、`token_invalid` の401を返します。Middleware内には `token_expired` を返す分岐もありますが、現在の処理順ではLibrary側の期限切れ判定が先に例外を返します。

署名検証によりTokenの改ざんと有効期限は検出しますが、現在 `iss` と `jti` の値は明示的に照合していません。Access Tokenを個別に失効させる仕組みもありません。

## 8. Refresh Token

Refresh TokenはUUIDのIDと32Byteのランダム値を `.` で連結した値です。生値は `refresh_token` Cookieだけへ返し、DBには値全体のSHA-256 Hashを保存します。発行元IP、User Agent、有効期限、失効日時も記録します。

| Cookie属性 | 現在の値 |
| --- | --- |
| Name | `refresh_token` |
| `HttpOnly` | `true` |
| `Secure` | `true` |
| `SameSite` | `None` |
| Path | `/api/v1/auth` |
| Domain | `REFRESH_COOKIE_DOMAIN`。未指定時はRequest Host |
| 有効期間 | 既定20,160分（14日） |

Refresh時はCookieのIDでDB Recordを取得し、Hash、有効期限、失効日時を検証します。成功すると旧Tokenへ `revoked_at` を設定し、新しいAccess TokenとRefresh Tokenを発行します。現在、このローテーション処理はDB Transactionや行Lockを使用していません。

Logout時はCookieが有効なRecordに対応する場合だけ失効日時を記録し、Cookieを同じPath・Domain属性で削除します。Cookieがない、または無効な場合も204を返します。

`tokens:prune` Commandは失効済みTokenと、期限切れから保持期間を過ぎたTokenを削除します。既定保持期間は30日で、Laravel Schedulerへ毎日00:00の実行として登録されています。実環境での定期実行には、Laravel Schedulerを起動する外部のCronまたはProcessが必要です。

## 9. API Request Guard、CORS、CSRF

`api.guard` はログイン、Refresh、Logoutへ適用し、次を確認します。

1. `ALLOWED_ORIGINS` が設定されている場合のOriginまたはReferer完全一致
2. `X-Requested-With: XMLHttpRequest`
3. `ENABLE_REFRESH_CSRF=true` の場合、RefreshとLogoutにおけるDouble Submit Cookie

`ALLOWED_ORIGINS` が空の場合、Origin検証は省略されます。CORSは別途 `CORS_ALLOWED_ORIGINS` を使用し、`api/*` へ適用します。Cookie送信を許可するため `supports_credentials=true` です。

任意のDouble Submit Cookieを有効にすると、JavaScriptから参照可能な `refresh_csrf` Cookieを発行し、RefreshとLogoutで `X-CSRF-Token` Headerとの一致を求めます。現在のNext.js ClientはこのHeaderを送信せず、CORSの許可Headerにも含まれていないため、現行連携では有効化できません。

`ALLOWED_ORIGINS` と `ENABLE_REFRESH_CSRF` は現在の `backend/laravel/.env.example` に記載されていません。

### 9.1 認証関連の環境変数

| 変数 | 用途 | 既定値・現在の設定 |
| --- | --- | --- |
| `SESSION_DRIVER` | MPA Sessionの保存先 | `.env.example` は `database` |
| `SESSION_LIFETIME` | SessionのIdle有効期間（分） | `.env.example` は120分 |
| `SESSION_ENCRYPT` | Session Payloadの暗号化 | `.env.example` は `false` |
| `SESSION_PATH` | Session CookieのPath | `.env.example` は `/` |
| `SESSION_DOMAIN` | Session CookieのDomain | `.env.example` は `null` |
| `SESSION_SECURE_COOKIE` | Session CookieのSecure属性 | `.env.example` に未記載、Config既定値は `null` |
| `SESSION_HTTP_ONLY` | Session CookieのHttpOnly属性 | `.env.example` に未記載、Config既定値は `true` |
| `SESSION_SAME_SITE` | Session CookieのSameSite属性 | `.env.example` に未記載、Config既定値は `lax` |
| `JWT_PRIVATE_KEY_PATH` | Access Token署名用秘密鍵のPath | 読み取れない場合はLoginまたはRefreshが500 |
| `JWT_PUBLIC_KEY_PATH` | Access Token検証用公開鍵のPath | 読み取れない場合は保護APIが500 |
| `JWT_ACCESS_TTL` | Access Tokenの有効期間（分） | 既定15分 |
| `JWT_REFRESH_TTL` | Refresh TokenとCookieの有効期間（分） | 既定20,160分 |
| `REFRESH_COOKIE_DOMAIN` | Refresh CookieのDomain | 未指定時はRequest Host |
| `REFRESH_TOKENS_RETAIN_DAYS` | 期限切れRefresh Tokenの保持日数 | 既定30日 |
| `CORS_ALLOWED_ORIGINS` | CORSで許可するOrigin | カンマ区切り、Config既定値は `http://localhost:3000` |
| `ALLOWED_ORIGINS` | `api.guard` で許可するOrigin | カンマ区切り、未設定時はGuardのOrigin検証を省略 |
| `ENABLE_REFRESH_CSRF` | Refresh・LogoutのDouble Submit Cookie | 既定 `false`、現在のNext.js Clientは有効化非対応 |

環境変数には秘密鍵本体やTokenを設定せず、鍵FileのPathと環境固有のDomain、Origin、有効期間だけを設定します。`ALLOWED_ORIGINS` と `CORS_ALLOWED_ORIGINS` は別の検証に使用するため、現在は両方の整合性を保つ必要があります。

## 10. 削除済みユーザーと認証状態

一般ユーザーのMPAログインとパスワード再設定では `is_deleted` を確認します。プロフィール処理は対象ユーザーの存在と本人一致を確認しますが、`is_deleted` を直接確認しません。管理画面から一般ユーザーを論理削除すると、`sessions.user_id` が一致するDatabase Sessionを削除します。

一方、現在のJWTログイン、Refresh、`me` は `is_deleted` を確認しません。論理削除時も `refresh_tokens` を失効させないため、削除済みユーザーがAPIへログインまたは既存Refresh Tokenを利用できる状態です。

管理者には現在、論理削除状態を表すカラムと管理機能がありません。

## 11. セキュリティ上の取扱い

- PasswordとRefresh Tokenの生値をDBへ保存しない
- JWT秘密鍵、CookieのToken、パスワード再設定Tokenをレスポンスやログへ不要に露出しない
- MPAのPOST FormはLaravel標準のCSRF保護を使用する
- ログイン成功時はSession IDを再生成する
- 認証・認可は画面表示だけに依存せず、LaravelのRoute Middlewareと処理側で確認する
- Access Tokenは短期間、Refresh Tokenとパスワード再設定Tokenは有効期限付きで扱う
- 環境固有のDomain、Origin、鍵Path、TTLは環境変数で管理する

この節は維持すべき方針です。現在実装との不一致は、次節の既知課題として管理します。

## 12. 既知の課題

以下は本書作成時に確認済みの実装課題です。改善後の仕様を現在実装として先取りせず、GitHub Issueの完了時に本書を更新します。

| Issue | 現在の課題 |
| --- | --- |
| [#10](https://github.com/DesignSupply/startify-app/issues/10) | `SigninController.php` と `SignInController` の大文字・小文字が一致しない |
| [#11](https://github.com/DesignSupply/startify-app/issues/11) | 認証を含むControllerへビジネスロジックが集中している |
| [#12](https://github.com/DesignSupply/startify-app/issues/12) | Password Reset Token用Modelの型付きPropertyによりClass読込時にFatal Errorとなる |
| [#13](https://github.com/DesignSupply/startify-app/issues/13) | 一般・管理者ログインに入力Validationがなく、認証失敗理由を個別に返す |
| [#14](https://github.com/DesignSupply/startify-app/issues/14) | 新規登録処理が確認メール内Tokenの照合済み状態を必須とせず、`email_verified_at` もMass Assignmentの対象外で保存されない |
| [#15](https://github.com/DesignSupply/startify-app/issues/15) | JWT認証が削除済みユーザーを拒否せず、論理削除時にRefresh Tokenを失効しない |
| [#16](https://github.com/DesignSupply/startify-app/issues/16) | 一般・管理者のパスワード再設定Tokenを生値でログ出力する |
| [#17](https://github.com/DesignSupply/startify-app/issues/17) | パスワード再設定メール送信がアカウント状態を外部へ示し、設定上の再発行Throttleも適用されない |
| [#18](https://github.com/DesignSupply/startify-app/issues/18) | ログアウト、パスワード変更、パスワード再設定時のSessionとRefresh Token失効方針が統一されていない |
| [#19](https://github.com/DesignSupply/startify-app/issues/19) | Refresh TokenローテーションにTransactionと行Lockがなく、同時更新で多重発行される可能性がある |
| [#20](https://github.com/DesignSupply/startify-app/issues/20) | JWTのIssuerなどのClaim検証と、期限切れを含むエラーレスポンスが整理されていない |
| [#21](https://github.com/DesignSupply/startify-app/issues/21) | API Guard、CORS、Refresh CSRFの環境設定とNext.js Clientの互換性が整理されていない |
| [#22](https://github.com/DesignSupply/startify-app/issues/22) | Refresh Token削除Commandの開発環境での手動実行方法と、本番環境でのCron運用が整備されていない |

次の項目も現在実装の制約です。実装方針を変更する場合は影響を精査し、必要に応じて別Issueで管理します。

- MPA認証、登録、再設定、JWT APIのFeature Testがない

## 13. 将来の認証基盤差し替え

現在の認証基盤はLaravelです。Next.jsはLaravel内部のGuardやModelではなく、認証APIのEndpoint、JSON、Cookie、Bearer Token、HTTP Statusの契約に依存します。

将来Cloudflare Workersなどへ認証・CRUDを移す場合、現在のAPI契約を維持できれば、Next.js側はAPI Base URLを中心に切り替えられます。ただし、Cookie Domain、CORS、CSRF、Token署名と検証、失効管理、Rate Limit、ユーザーData Storeまで自動的に互換になるわけではありません。Cloudflare Accessのように用途と認証方式が異なるサービスを採用する場合は、Laravel認証との責務分担を別途設計します。

これは将来案であり、現在の実装方針をCloudflareへ確定するものではありません。

## 14. 検証

Docker環境起動後、`server/` でLaravelの自動テストとRoute一覧を確認します。

```bash
make laravel-test
```

```bash
make laravel-route
```

現在、Laravel認証機能を直接検証するFeature Testはありません。認証変更時は、少なくとも次の結合フローを対象にします。

- 一般ユーザーと管理者のログイン、保護画面、ログアウト
- 新規登録メール送信、Token確認、登録、重複メール
- 一般・管理者のパスワード再設定と無効・期限切れToken
- APIの `login → me → refresh → me → logout → me（401）`
- 削除済み一般ユーザーのMPA認証とAPI認証
- Origin、必須Header、Cookie属性、Rate Limit

## 15. 移行元資料

本書は、次の既存資料から設計意図を抽出し、現在のコード、設定、Migration、Next.js側仕様と照合して再構成しています。

- `specifications/backend/laravel/TASK_003.md`
- `specifications/backend/laravel/TASK_004.md`
- `specifications/backend/laravel/TASK_005.md`
- `specifications/backend/laravel/TASK_006.md`
- `specifications/backend/laravel/TASK_014.md`
- `.cursor/rules/app-overview.mdc`
- `.cursor/rules/dev-backend.mdc`

これらはドキュメント移行が完了するまで設計意図の確認に使用しますが、認証の現在仕様としては本書と現在の実装を優先します。
