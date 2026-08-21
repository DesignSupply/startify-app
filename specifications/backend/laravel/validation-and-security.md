---
title: Laravelアプリケーション Validation・Security仕様
status: current
last_updated: 2026-08-21
related_paths:
  - backend/laravel/.env.example
  - backend/laravel/app/Console/Commands/
  - backend/laravel/app/Http/Controllers/
  - backend/laravel/app/Http/Middleware/
  - backend/laravel/app/Http/Requests/
  - backend/laravel/app/Models/
  - backend/laravel/bootstrap/app.php
  - backend/laravel/config/
  - backend/laravel/resources/views/
  - backend/laravel/routes/
  - backend/laravel/tests/
  - backend/laravel/composer.json
  - backend/laravel/composer.lock
  - server/docker/nginx/nginx.conf
  - server/docker/php/php.ini
---

# Laravelアプリケーション Validation・Security仕様

Startify-AppのLaravelアプリケーションにおける、入力検証、認証・認可境界、CSRF、Cookie、CORS、JWT、出力、ファイル、ログ、依存パッケージに関する現在仕様と、機能変更時に維持する横断方針を定義します。

本書は横断的な防御と検証責務を扱います。認証フローとTokenの詳細は `specifications/backend/laravel/authentication.md`、問い合わせとメールは `specifications/backend/laravel/contact-and-mail.md`、投稿・カテゴリ・タグは `specifications/backend/laravel/content-management.md`、ファイルは `specifications/backend/laravel/file-management.md` を正本とします。

## 1. 適用範囲と信頼境界

現在のLaravelアプリケーションには、次のInterfaceと信頼境界があります。

| Interface | 主な入口 | 現在の保護 |
| --- | --- | --- |
| Public Web | `backend/laravel/routes/web.php` の公開Route | `web` Middleware、POSTのCSRF、Endpointごとの入力検証 |
| 一般ユーザーWeb | `auth` Route Group | `web` Session Guard、Controllerでの本人確認 |
| 管理者Web | `auth:admin` Route Group | `admin` Session Guard、管理者Route境界 |
| 共通閲覧Web | `auth.any` | 一般ユーザーまたは管理者のSession認証 |
| 認証API | `/api/v1/auth/*` | CORS、Endpointに応じたAPI Request Guard、Rate Limit、JWT、Refresh Token |
| Console | Artisan Command、Scheduler | コンテナーまたは運用環境からの実行 |
| Storage・DB・SMTP | Laravelから接続する内部資源 | Configと環境変数、Model、Filesystem、Notification |

利用者から受け取るRequest、Header、Cookie、URL Parameter、Upload File、メールアドレスは、検証前の値として扱います。Bladeへ渡す文字列、Storage Path、DB検索条件、ログ内容、外部送信内容へ使用する前に、用途に応じた検証またはEscapeを行います。

## 2. Validationの配置

現在はForm RequestとController内の `$request->validate()` が併存しています。

### 2.1 Form Request

`backend/laravel/app/Http/Requests/` のForm Requestは次の機能で使用します。

| 機能 | Form Request |
| --- | --- |
| 問い合わせ | `ContactFormRequest` |
| ファイル登録・更新 | `FileUploadRequest`、`FileUpdateRequest` |
| 投稿登録・更新 | `PostStoreRequest`、`PostUpdateRequest` |
| カテゴリ登録・更新 | `CategoryStoreRequest`、`CategoryUpdateRequest` |
| タグ登録・更新 | `TagStoreRequest`、`TagUpdateRequest` |

各Form Requestの `authorize()` は現在すべて `true` を返します。認可はForm Requestではなく、主にRoute MiddlewareとControllerで行います。入力整形用の `prepareForValidation()` などは使用していません。

### 2.2 Controller内Validation

次の機能はController内で入力を検証します。

- 新規ユーザー登録
- 一般ユーザー・管理者のパスワード再設定
- 一般ユーザー・管理者のプロフィール更新
- 管理者による一般ユーザー更新
- JWT APIログイン
- パスワード再設定メール送信

一般ユーザー・管理者のMPAログインは、現在明示的な入力Validationを行わず、Requestから取得したメールアドレスとパスワードを認証処理へ渡します。この改善はIssue #13で管理します。

### 2.3 現在の主なRule

| 対象 | 現在の主なRule・制約 |
| --- | --- |
| 名前 | 必須、文字列、最大255文字 |
| メールアドレス | 機能により必須、`email`、Unique。最大長と `string` の指定は統一されていない |
| パスワード | 機能により必須または任意、最小8文字、確認入力一致。型と最大長は統一されていない |
| 電話番号 | 任意、数字10〜11桁 |
| URL | 任意、`url` |
| 問い合わせ種別 | 任意、配列、各要素は文字列 |
| 問い合わせ本文 | 必須、文字列。現在は最大長なし |
| 投稿タイトル | 必須、文字列、最大255文字 |
| 投稿本文 | 必須、文字列。現在は最大長なし |
| 公開日時 | 必須、日付 |
| カテゴリ・タグ選択 | 任意、配列、各IDが対象テーブルに存在すること |
| カテゴリ・タグ名 | 必須、文字列、最大255文字 |
| Slug | 必須、英数字とハイフン、最大255文字、Unique |
| Upload File | 必須、File、最大10,240KB、許可形式を `mimes` で検証 |
| ファイル説明 | 任意、文字列、最大1,000文字 |

機能別の正確なRule、Error Message、既知の上限不足は各機能仕様を参照します。

## 3. Validationの共通方針

新規機能または既存機能を変更するときは、次を基本とします。

- 副作用を実行する前に、必須、型、形式、長さ、件数、存在、重複を検証する
- Client側のHTML属性だけに依存せず、Server側で検証する
- 文字列、配列、Fileなど、後続処理が期待する型を明示する
- DBの型・長さ・Unique制約とApplication Validationを整合させる
- DBのUnique制約や外部キーは、競合時を含む最終的な整合性保証として維持する
- 選択肢は `in`、Relation IDは `exists` など、許可対象を明示する
- 配列入力は配列全体の件数と各要素の型・値・重複を確認する
- Fileは容量だけでなく、MIME Type、保存拡張子、元ファイル名、画像寸法、処理環境を確認する
- Error ResponseやMessageから、認証情報、登録有無、内部例外、秘密情報を不必要に公開しない
- 複数画面フローでは、画面表示時だけでなく確定処理の直前にも状態を再検証する
- 複雑な入力検証はForm Requestへの分離を検討し、認可境界をRoute、Policy、Controllerのいずれで担うか明確にする

これらは維持・採用する横断方針です。現在実装との不一致は、本書および各機能仕様の既知Issueで管理します。

## 4. Web認証と認可

MPAは `backend/laravel/config/auth.php` の2つのSession Guardを使用します。

| Guard | Provider | 主なRoute境界 |
| --- | --- | --- |
| `web` | `users` | `auth`、`guest` |
| `admin` | `admin_users` | `auth:admin` |

`AuthenticateAny` は `web` または `admin` のどちらかが認証済みであれば投稿閲覧を許可します。未認証の場合は一般ユーザーログインへRedirectします。

管理者のファイル、一般ユーザー、投稿、カテゴリ、タグ管理は `auth:admin` の内側にあります。一般ユーザーと管理者のプロフィール編集では、Routeの認証に加えてControllerがログイン中のIDと対象IDの一致を確認します。

Form Requestの `authorize()` は認可を行わないため、Form Requestを別Routeで再利用するときは、同等のMiddlewareまたは認可処理が適用されることを確認します。

現在の認証可否、削除状態、メール確認状態、ログアウトとSession失効の詳細は認証仕様とIssue #13〜#18、#32で管理します。

## 5. Web CSRF

`backend/laravel/routes/web.php` のRouteにはLaravel標準の `web` Middleware Groupが適用されます。現在のBladeによるPOST Formは `@csrf` を使用し、CSRF Tokenを送信します。

変更操作はGETではなくPOSTで実装されています。削除・復元もPOST Formを使用します。

Web Routeを追加または変更するときは、次を維持します。

- 状態変更をGET Routeへ置かない
- Blade Formには `@csrf` を含める
- JavaScriptからWeb Routeへ送信する場合もCSRF Tokenを送る
- CSRF除外を追加する場合は、対象Path、代替防御、外部公開範囲を明示する

## 6. SessionとWeb Cookie

現在のSession設定は `backend/laravel/config/session.php` で定義します。

| 設定 | 現在値・既定値 |
| --- | --- |
| Driver | `database` |
| Lifetime | 120分 |
| Browser終了時失効 | `false` |
| Session暗号化 | `false` |
| Cookie Path | `/` |
| Secure | `SESSION_SECURE_COOKIE`。ローカルHTTPS応答では有効 |
| HttpOnly | `true` |
| SameSite | `lax` |
| Partitioned | `false` |

ローカルHTTPSの実行確認では、Session CookieにSecure、HttpOnly、SameSite=Laxが付与されます。Laravelが発行する `XSRF-TOKEN` はJavaScriptから参照する用途のためHttpOnlyではなく、Secure、SameSite=Laxです。

ログイン成功時は一般ユーザー・管理者ともSession IDを再生成します。現在のログアウトはGuardからLogoutしてCSRF Tokenを再生成しますが、Session全体をInvalidateしません。パスワード変更・再設定時を含む失効方針はIssue #18で管理します。

Session Payloadは現在暗号化されないため、秘密情報を安易にSessionへ保存しません。新規登録と問い合わせでは複数画面フローの一時データをSessionに保存しており、詳細と既知課題は各機能仕様を参照します。

## 7. API Request GuardとCORS

認証APIは `backend/laravel/config/cors.php` により `api/*` へCORSを適用します。

| 設定 | 現在値 |
| --- | --- |
| Allowed Origins | `CORS_ALLOWED_ORIGINS`。既定は `http://localhost:3000` |
| Allowed Methods | GET、POST、PUT、PATCH、DELETE、OPTIONS |
| Allowed Headers | Content-Type、X-Requested-With、Authorization、Accept、Origin |
| Credentials | `true` |
| Preflight Cache | 0秒 |

`VerifyApiRequestGuard` はログイン、Refresh、Logoutに適用し、次を確認します。

1. `ALLOWED_ORIGINS` が設定されている場合、OriginまたはReferer由来のOriginが完全一致すること
2. `X-Requested-With: XMLHttpRequest` があること
3. RefreshまたはLogoutで `ENABLE_REFRESH_CSRF` が有効な場合、`refresh_csrf` Cookieと `X-CSRF-Token` Headerが一致すること

現在、`ALLOWED_ORIGINS` と `ENABLE_REFRESH_CSRF` は `.env.example` に定義されていません。`ALLOWED_ORIGINS` が空の場合はOrigin検証を行わず、Refresh CSRFも既定で無効です。また、CORSのAllowed Headersに `X-CSRF-Token` がないため、現在の構成のままRefresh CSRFを有効化するとBrowserのPreflightと互換しません。

CORS、API Request Guard、Refresh CSRF、Config集約の改善はIssue #21で管理します。

## 8. JWTとRefresh Token

Access Tokenは `firebase/php-jwt` を使用し、RS256で署名します。秘密鍵と公開鍵はGit管理対象外のFile Pathから読み込みます。Access TokenはJSON Response本文で返し、Refresh TokenはCookieで配布します。

JWT Middlewareは次を確認します。

- Authorization HeaderがBearer形式であること
- 公開鍵を読み込めること
- RS256署名を検証できること
- `nbf` と `exp` が現在時刻に対して有効であること
- `sub` Claimが存在すること

現在、IssuerはAccess Tokenへ含めますがMiddlewareで照合していません。期限切れを含むDecode例外は共通の `token_invalid` として処理される場合があります。この改善はIssue #20で管理します。

Refresh Tokenの生値はCookieへ返し、DBにはSHA-256 Hashを保存します。Cookieは次の属性を使用します。

| Cookie | Path | Secure | HttpOnly | SameSite |
| --- | --- | --- | --- | --- |
| `refresh_token` | `/api/v1/auth` | `true` | `true` | `None` |
| `refresh_csrf` | `/api/v1/auth` | `true` | `false` | `None` |

Refresh Tokenは有効期限、失効日時、置換先、IP Address、User AgentをDBで管理します。Refresh時は使用中Tokenを失効して新しいTokenを発行しますが、現在はTransactionと行Lockを使用しないため、同時Requestで多重発行される可能性があります。Issue #19で管理します。

API認証のController、Middleware、Token削除Commandは現在、JWT、Cookie、Origin、有効期間などをApplication Codeから直接 `env()` で参照します。Config Cacheとの互換性を含む改善はIssue #21で管理します。

## 9. Rate Limit

現在、次のAPI RouteにRate Limitがあります。

| Route | Limiter | 現在値 | Key |
| --- | --- | --- | --- |
| `POST /api/v1/auth/login` | `login` | 5回/分 | メールアドレスとIP Address |
| `POST /api/v1/auth/refresh` | `refresh` | 5回/分 | IP Address |

API Logoutと`/me`、MPAログイン、新規登録確認メール、パスワード再設定、問い合わせにはRoute Rate Limitがありません。Password Brokerには60秒のThrottle設定がありますが、現在のメール送信処理は `createToken()` を直接使用し、Brokerの送信Throttleを適用しません。

認証・問い合わせの再送制限はIssue #17、#23、#32、API Guardとの組み合わせはIssue #21で管理します。

## 10. HTML出力とXSS対策

Bladeの `{{ ... }}` による通常出力はHTML Escapeされます。現在の画面とメールTemplateは、名前、メールアドレス、投稿、問い合わせ、ファイルMetadataなどの利用者入力を原則として通常出力します。

Raw出力 `{!! ... !!}` は、投稿本文と問い合わせ本文の改行を `<br>` へ変換する箇所で使用します。現在は次の順序で処理します。

1. `e()` で利用者入力をEscapeする
2. `nl2br()` で改行を `<br>` へ変換する
3. 処理済みHTMLだけをRaw出力する

利用者入力を直接 `{!! ... !!}` へ渡しません。HTMLを許可する機能を追加する場合は、許可Tag・Attribute・URL Schemeを定義したSanitize処理を導入し、単純なRaw出力で実装しません。

## 11. DB QueryとMass Assignment

現在のDB操作は主にEloquent、Query Builder、Parameter Bindingを使用します。プロフィール取得の `whereRaw()` もPlaceholder Bindingを使用し、利用者入力をSQL文字列へ直接連結しません。

Modelの `$fillable`、`$hidden`、`$casts` は機能に応じて定義します。PasswordとRemember Tokenは認証Modelの配列・JSON出力から隠します。

Mass Assignment対象を追加するときは、入力値をそのまま `create()` や `update()` へ渡さず、Validation済みのFieldと内部生成値を区別します。複数のDB・Storage操作を伴う処理では、Transactionと補償処理の境界を確認します。

## 12. File UploadとStorage

管理者ファイル機能は `auth:admin` とWeb CSRFの内側にあります。Upload Fileは10MB以下と許可形式を検証し、非公開の `uploads` Diskへ保存します。Downloadは管理者認証済みRouteからAttachment Responseとして返します。

NginxとPHPの受信上限は64MB、Laravel Validationは10MBであり、最も小さいLaravelの上限が機能上の制限です。

現在のValidationと保存拡張子の情報源、StorageとDBの失敗時整合性、画像寸法とGD対応形式には既知課題があります。詳細はファイル管理仕様とIssue #28〜#30を参照します。

## 13. Loggingと秘密情報

標準Log Channelは `stack`、既定の出力先は `single`、Levelは `.env.example` でDebugです。Application Logへは、処理結果、対象ID、例外情報などを必要な範囲で記録します。

次をGit管理対象またはLogへ記録しません。

- `.env` の実値
- JWT秘密鍵・公開鍵の内容
- 生のAccess Token・Refresh Token
- Password、SMTP認証情報、API Token
- Session CookieとCookie内Token
- Upload File内容とBase64 Data
- 不要な問い合わせ本文や個人情報

現在、一般ユーザー・管理者のパスワード再設定メール送信成功時に、メールアドレスと生の再設定TokenをLogへ出力します。この改善はIssue #16で管理します。

JWT Decode失敗時は例外MessageをWarning Logへ記録しますが、Clientには共通の認証エラーを返します。Client ResponseへStack Traceや内部Pathを返さないようにします。

## 14. 環境変数とConfig

環境固有値と秘密情報は環境変数で注入し、追跡対象の `.env.example` には変数名と開発用の非秘密値だけを記載します。

LaravelのConfig Fileでは `env()` を使用できますが、Controller、Middleware、Model、Service、CommandなどのApplication Codeは `config()` から取得する構成を基本とします。現在のAPI認証領域には直接 `env()` 参照が残り、Issue #21でConfigへ集約します。

Configや環境変数を変更するときは、未Cache状態とConfig Cache使用時の両方を考慮します。秘密鍵、証明書、Token、実アカウント情報、SMTP PasswordをGitへ追加しません。

## 15. 依存パッケージ

PHP依存は `backend/laravel/composer.json` と `backend/laravel/composer.lock` で管理します。依存変更時はLock Fileを確認し、意図しないMajor Updateや無関係なPackage更新を混在させません。

2026年8月21日時点の `composer audit --locked` は、13 Packageに影響する40件のSecurity Advisoryを検出し、終了コード1です。現在の主なVersionはLaravel 11.42.1、`firebase/php-jwt` 6.11.1、PHPUnit 11.5.7です。

現行Major内の依存更新と残存Advisoryの申し送りはIssue #33で管理します。Laravelの継続、サポート対象MajorへのUpgrade、`firebase/php-jwt` 7系へのUpgradeは、Issue #33完了時に登録する後続Issueで扱います。

## 16. ローカル環境と本番要件の境界

現在の `server/` はローカルDocker環境です。

| 項目 | 現在のローカル設定 |
| --- | --- |
| HTTPS | 自己署名証明書を使用 |
| TLS | TLS 1.2、1.3 |
| PHP Error表示 | `display_errors = On` |
| Laravel Debug | `.env.example` で `APP_DEBUG=true` |
| Nginx/PHP Version Header | Responseへ出力される |
| Security Header | HSTS、CSP、`X-Content-Type-Options` などは未設定 |

これらはローカル開発環境の現在設定であり、本番環境へそのまま適用する要件ではありません。Laravelを本番配信する場合は、DebugとError表示を無効化し、TLS終端、Proxy、Security Header、Cookie Domain、CORS、Secrets、Log、監視、Rate Limitを本番Infrastructure仕様として別途定義します。

現在、Laravelの本番配信構成とLaravel向けGitHub Actions CIはありません。

## 17. テストと検証

Docker環境起動後、`server/` で次を確認します。

```bash
make ps
```

```bash
make laravel-route
```

```bash
make laravel-test
```

依存パッケージを変更するときは、Laravel Directoryまたはコンテナー内で次も確認します。

```bash
composer validate --strict
```

```bash
composer audit --locked
```

2026年8月21日時点の自動テストはExample Test 2件、2 Assertionだけです。Validation、認証、認可、CSRF、CORS、JWT、Rate Limit、Upload、Escape、Loggingを直接検証するTestはありません。

機能変更時は正常系だけでなく、未認証、権限不足、不正な型、長さ超過、重複、期限切れ、再利用、同時Request、直接POST、外部状態の失敗を変更範囲に応じて確認します。

## 18. 既知の課題

以下は本書作成時に確認済みの横断的なValidation・Security課題です。改善後は本文を現在仕様へ更新し、解決済みIssueをこの一覧から削除します。

| Issue | 現在の課題 |
| --- | --- |
| [#11](https://github.com/DesignSupply/startify-app/issues/11) | Controllerにビジネスロジックと副作用が集中し、Validation後の処理、Transaction、失敗時補償の境界が統一されていない |
| [#12](https://github.com/DesignSupply/startify-app/issues/12) | 未使用のパスワード再設定Token Modelを読み込むと、互換性のないProperty型によりFatal Errorになる |
| [#13](https://github.com/DesignSupply/startify-app/issues/13) | MPAログインに明示的な入力Validationがなく、認証失敗Messageからアカウント状態を推測できる |
| [#14](https://github.com/DesignSupply/startify-app/issues/14) | 新規登録でメール内Tokenの照合結果を登録条件へ使用せず、メール未確認でも `email_verified_at` が保存される |
| [#15](https://github.com/DesignSupply/startify-app/issues/15) | JWT APIが論理削除済みユーザーを拒否せず、削除時にRefresh Tokenを失効しない |
| [#16](https://github.com/DesignSupply/startify-app/issues/16) | 生のパスワード再設定TokenとメールアドレスをLogへ出力する |
| [#17](https://github.com/DesignSupply/startify-app/issues/17) | パスワード再設定メールが登録状態を公開し、Password BrokerのThrottleを適用しない |
| [#18](https://github.com/DesignSupply/startify-app/issues/18) | ログアウト、パスワード変更・再設定後のSessionとRefresh Token失効方針が統一されていない |
| [#19](https://github.com/DesignSupply/startify-app/issues/19) | Refresh Tokenの同時更新をTransactionと行Lockで直列化していない |
| [#20](https://github.com/DesignSupply/startify-app/issues/20) | JWTのIssuer照合と期限切れError処理が不足している |
| [#21](https://github.com/DesignSupply/startify-app/issues/21) | CORS、Origin、Refresh CSRF、環境変数、Config Cacheの設定が整合していない |
| [#22](https://github.com/DesignSupply/startify-app/issues/22) | 期限切れRefresh Token削除Commandの開発・本番実行方法が整備されていない |
| [#23](https://github.com/DesignSupply/startify-app/issues/23) | 問い合わせ送信が検証済みSession Dataだけを使用せず、連続送信制限もない |
| [#24](https://github.com/DesignSupply/startify-app/issues/24) | 問い合わせ宛先とメール設定、送信失敗時の状態管理が整理されていない |
| [#26](https://github.com/DesignSupply/startify-app/issues/26) | 論理削除済みカテゴリ・タグが `exists` Validationを通過し、投稿へ関連付けできる |
| [#27](https://github.com/DesignSupply/startify-app/issues/27) | 投稿本文とカテゴリ・タグ配列の長さ、件数、型、重複Validationが不足している |
| [#28](https://github.com/DesignSupply/startify-app/issues/28) | StorageとDBの操作失敗時にファイル実体とMetadataが不整合になる可能性がある |
| [#29](https://github.com/DesignSupply/startify-app/issues/29) | Upload Validationと保存拡張子の情報源が一致していない |
| [#30](https://github.com/DesignSupply/startify-app/issues/30) | 画像寸法、GD対応形式、処理失敗、Resource消費の防御とTestが不足している |
| [#31](https://github.com/DesignSupply/startify-app/issues/31) | パスワード再設定で文字列以外のPasswordがValidationを通過し、500 Errorになる可能性がある |
| [#32](https://github.com/DesignSupply/startify-app/issues/32) | メールアドレス・パスワード変更時の本人確認とメール所有確認がなく、管理者メールアドレスも変更できる |
| [#33](https://github.com/DesignSupply/startify-app/issues/33) | Composer Lockが古く、現行Major内で更新可能なSecurity Advisoryが残っている |

次も現在実装の制約です。Laravelの継続利用、本番配信、CI整備を決めるときにIssue化を検討します。

- Laravel 11のSecurity Supportが終了している
- Laravel向けCIと実質的なSecurity Testがない
- MPAログイン、新規登録、問い合わせなど、API以外の公開POST Routeに共通Rate Limitがない
- 標準的なSecurity Headerを付与する本番Web Server構成がない
- 本番環境のDebug、Error表示、Log、監視要件が未定義である

## 19. 関連仕様書

| 領域 | 正本 |
| --- | --- |
| Laravel構造、レイヤー、配置 | `specifications/backend/laravel/architecture.md` |
| 画面、Route、Access境界 | `specifications/backend/laravel/screens-and-features.md` |
| Table、制約、Transaction | `specifications/backend/laravel/database.md` |
| Session、Guard、新規登録、Password Reset、JWT | `specifications/backend/laravel/authentication.md` |
| 問い合わせ、Notification、SMTP、Mailpit | `specifications/backend/laravel/contact-and-mail.md` |
| 投稿、カテゴリ、タグ | `specifications/backend/laravel/content-management.md` |
| Upload、Storage、Preview、Download | `specifications/backend/laravel/file-management.md` |
| 一般・管理者プロフィール、一般ユーザー管理 | `specifications/backend/laravel/user-and-profile-management.md` |
| Next.jsの認証Client | `specifications/frontend/next/authentication.md` |
| Docker、Nginx、PHP | `specifications/server/docker/overview.md`、`specifications/server/docker/setup-and-operations.md`、`server/` |
