---
title: Laravelアプリケーション 問い合わせ・メール仕様
status: current
last_updated: 2026-08-15
related_paths:
  - backend/laravel/.env.example
  - backend/laravel/app/Http/Controllers/ContactController.php
  - backend/laravel/app/Http/Requests/ContactFormRequest.php
  - backend/laravel/app/Notifications/
  - backend/laravel/config/mail.php
  - backend/laravel/resources/views/emails/
  - backend/laravel/resources/views/pages/contact/
  - backend/laravel/routes/web.php
  - backend/laravel/tests/
  - server/docker-compose.yml
  - server/docker/mailpit/
  - server/docker/php/Dockerfile
  - server/docker/php/php.ini
---

# Laravelアプリケーション 問い合わせ・メール仕様

Startify-AppのLaravelアプリケーションが提供する問い合わせフォームと、Notification・Blade Template・SMTPを使用したメール送信の現在仕様を定義します。

本書は問い合わせ機能とLaravel共通のメール実装方針を扱います。問い合わせ画面とRouteの索引は `specifications/backend/laravel/screens-and-features.md`、認証メールのToken・有効期限・送信条件は `specifications/backend/laravel/authentication.md` を参照してください。

## 1. メール送信の構成

現在のLaravelメールは、Laravel NotificationのMail Channel、`MailMessage`、BladeメールTemplateを使用します。

```text
Controller
  ↓ Notificationを生成
Notification::route() または Notifiable Model
  ↓ Mail Channel
MailMessage
  ↓ BladeメールTemplate
Laravel SMTP Mailer
  ↓
開発環境: Mailpit
開発環境以外: 環境変数でSMTP接続先を設定可能
```

開発環境のMailpitは現在のDocker構成です。本番環境のSMTP Server、配信Service、認証方法、運用手順はリポジトリ内に定義されていません。

現在のNotificationは `Queueable` Traitを使用しますが、`ShouldQueue` を実装していないため、すべてHTTP Request内で同期送信します。問い合わせ内容を保存する専用テーブルはありません。

## 2. メール一覧

| 用途 | Notification | Template | 宛先 | 件名 |
| --- | --- | --- | --- | --- |
| 問い合わせ自動返信 | `ContactFormReplyNotification` | `emails.contact-reply` | 問い合わせフォームのメールアドレス | お問い合わせありがとうございます |
| 問い合わせ管理者通知 | `ContactFormAdminNotification` | `emails.contact-notification` | 現在は `admin@example.com` 固定 | ウェブサイトからお問い合わせがありました |
| 新規登録確認 | `SignUpNotification` | `emails.signup-verify` | 登録予定のメールアドレス | `APP_NAME`を含む新規ユーザー登録のお知らせ |
| 一般ユーザーパスワード再設定 | `PasswordResetNotification` | `emails.password-reset` | 一般ユーザーのメールアドレス | `APP_NAME`を含むパスワードリセットのお知らせ |
| 管理者パスワード再設定 | `AdminPasswordResetNotification` | `emails.admin-password-reset` | 管理者のメールアドレス | `APP_NAME`を含む管理者パスワードリセットのお知らせ |

問い合わせメールは `Notification::route('mail', $address)` を使用します。認証メールは `Notifiable` を使用する `User` または `AdminUser`へNotificationを送ります。

## 3. 問い合わせ画面とRoute

問い合わせ機能は認証不要のWeb Routeとして公開されています。

| Method / Path | Route Name | Controller Action | 用途 |
| --- | --- | --- | --- |
| `GET /contact` | `contact` | `index` | 入力画面を表示 |
| `POST /contact/form` | `contact.form` | `form` | 入力を検証してSessionへ保存 |
| `GET /contact/confirm` | `contact.confirm` | `confirm` | Sessionの入力を確認表示 |
| `POST /contact/send` | `contact.send` | `send` | 自動返信と管理者通知を送信 |
| `GET /contact/thanks` | `contact.thanks` | `thanks` | 送信完了画面を表示 |

POST FormはBladeの `@csrf` によりLaravel標準のCSRF Tokenを送信します。問い合わせRouteには現在、認証Middleware、Rate Limit、Bot判定を指定していません。

## 4. 入力項目とValidation

`POST /contact/form` は `ContactFormRequest` を使用します。

| Field | UI | 現在のValidation | 必須 |
| --- | --- | --- | --- |
| `name` | Text | 文字列、最大255文字 | 必須 |
| `company` | Text | 文字列、最大255文字 | 任意 |
| `email` | Email | メールアドレス形式 | 必須 |
| `phone` | Tel | 10〜11桁の数字 | 任意 |
| `url` | URL | URL形式 | 任意 |
| `inquiry_type` | Checkbox配列 | 配列 | 任意 |
| `inquiry_type.*` | Checkbox | 文字列 | 任意 |
| `gender` | Radio | `男性` または `女性` | 必須 |
| `message` | Textarea | 文字列 | 必須 |

画面の問い合わせ種別は現在 `種別1`、`種別2`、`種別3` です。ただし、Server側は `inquiry_type.*` の許可値と配列件数を制限していません。`email`、`url`、`message` にも明示的な最大文字数はありません。

Form Requestの `authorize()` は常に `true` を返します。属性名と日本語エラーメッセージもForm Requestで定義します。

## 5. 入力・確認・送信フロー

### 5.1 入力とSession保存

入力画面はValidation ErrorとOld Inputを表示します。`POST /contact/form` はValidation成功後、現在Requestの全入力を `contact_form_inputs` としてSessionへ保存し、確認画面へRedirectします。

現在は `$request->all()` を保存するため、検証対象外の入力やCSRF TokenもSession Payloadへ含まれます。

### 5.2 確認画面

`GET /contact/confirm` は `contact_form_inputs` がない場合、入力画面へRedirectします。Sessionの各入力値をEscapeして表示し、同じ値をHidden Fieldとして送信Formへ埋め込みます。

問い合わせ本文は通常のBlade Escapeで表示します。改行をHTMLの改行へ変換する処理は確認画面では行いません。

### 5.3 メール送信

`POST /contact/send` はPOSTデータとSessionを取得し、必須3項目がPOSTにあればPOSTを優先します。POSTに必要な項目がなく、SessionがあればSessionを使用します。どちらもない場合は入力画面へRedirectします。

送信時に `ContactFormRequest` を再適用しないため、確認画面のHidden Fieldまたは送信Endpointへの直接POSTで、検証済みの内容と異なる値を送信できます。

送信データにはRequest時点のIP AddressとUser Agentを追加します。次の順に同期送信します。

1. 問い合わせ者への自動返信
2. `admin@example.com` への管理者通知

2通とも例外なく送信できた場合、`contact_form_sent` をSessionへ保存し、`contact_form_inputs` を削除して完了画面へRedirectします。

例外が発生した場合は入力画面へRedirectし、メール送信失敗のMessageを表示します。例外は現在Logへ記録しません。

### 5.4 完了画面

`GET /contact/thanks` は `contact_form_sent` がない場合、入力画面へRedirectします。表示時にFlagをSessionから削除するため、同じSessionで完了画面を再読み込みすると入力画面へRedirectします。

## 6. 問い合わせメール

### 6.1 自動返信

自動返信は、問い合わせフォームに入力されたメールアドレスへ送信します。本文には次を表示します。

- 名前
- 会社名
- メールアドレス
- 電話番号
- Web Site URL
- 問い合わせ種別
- 性別
- 問い合わせ本文

任意項目は値がある場合だけ表示します。問い合わせ本文は `e()` でEscapeした後、`nl2br()` で改行を表示します。

### 6.2 管理者通知

管理者通知は現在 `admin@example.com` へ送信します。自動返信の内容に加え、次を表示します。

- IP Address
- User Agent
- Notification生成時の送信日時

管理者宛先は現在、環境変数またはConfigへ分離されていません。

### 6.3 部分的な送信成功

自動返信の後に管理者通知を送るため、自動返信が成功し、管理者通知だけが失敗する場合があります。この場合も画面は送信失敗として入力画面へ戻るため、利用者が再送すると自動返信が重複する可能性があります。

問い合わせ内容をDBへ保存せず、配送状態も永続管理しないため、現在は2通の個別の配送状態を追跡できません。

## 7. 認証メール

新規登録確認、一般ユーザーと管理者のパスワード再設定も、問い合わせと同じ既定SMTP MailerとBladeメールTemplateを使用します。

- Notificationで件名、Template、Template Dataを構成する
- Token付きURLはNotificationで生成する
- Tokenの検証、Session、Password Broker、有効期限は認証仕様を正本とする
- 問い合わせ固有の管理者宛先や送信状態を認証メールへ適用しない

認証メールの既知課題は `specifications/backend/laravel/authentication.md` と関連GitHub Issueで管理します。

## 8. メール設定

`backend/laravel/config/mail.php` は既定MailerとしてSMTPを使用します。

| 環境変数 | 用途 | `.env.example`の現在値・状態 |
| --- | --- | --- |
| `MAIL_MAILER` | 既定Mailer | `smtp` |
| `MAIL_SCHEME` | SMTP接続Scheme | 未記載 |
| `MAIL_URL` | SMTP接続URL | 未記載 |
| `MAIL_HOST` | SMTP Host | `smtp` |
| `MAIL_PORT` | SMTP Port | `1025` |
| `MAIL_USERNAME` | SMTP Username | `null` |
| `MAIL_PASSWORD` | SMTP Password | `null` |
| `MAIL_EHLO_DOMAIN` | SMTP EHLO Domain | 未記載 |
| `MAIL_FROM_ADDRESS` | 共通From Address | `no-reply@example.com` |
| `MAIL_FROM_NAME` | 共通From Name | `${APP_NAME}` |
| `MAIL_ENCRYPTION` | 旧来の暗号化設定名 | `.env.example` に `null` があるが、現在の `config/mail.php` は参照しない |

`config/mail.php` のSMTP設定は `MAIL_SCHEME` を参照します。現在の `.env.example` は `MAIL_ENCRYPTION` を記載しているため、暗号化方式を設定する際は変数名の不一致に注意が必要です。

本番環境のSMTP Host、認証情報、From Address、問い合わせ管理者宛先は環境変数で管理し、実値をGit管理対象へ追加しません。現在、問い合わせ管理者宛先用の環境変数はありません。

## 9. 開発環境とMailpit

Docker Composeの `smtp` ServiceはMailpitを実行します。

| 用途 | 接続先 |
| --- | --- |
| LaravelからのSMTP | Docker Network内の `smtp:1025` |
| HostからのSMTP | `localhost:1025` |
| Mailpit Web UI | `http://localhost:8025` |

Mailpitは開発用のメール受信・表示環境です。開発中に実在する外部アドレスへメールを配信しません。

PHP ImageにもMailpit Binaryと `sendmail_path` が設定されていますが、現在のLaravelはSMTP Mailerを使用するため、問い合わせ・認証NotificationはDocker Composeの `smtp` Serviceへ送信します。PHPのSendmail設定はLaravelの現在経路では使用しません。

## 10. メール実装の共通方針

新しいメール送信機能または既存機能の変更では、次を基本とします。

- 件名、Template、Template DataはNotificationへ置く
- 本文は `backend/laravel/resources/views/emails/` のBladeへ置く
- 宛先は検証済みData、Notifiable Model、またはConfigから取得する
- 本番固有の宛先、SMTP認証情報、From Addressをコードへ固定しない
- 利用者入力をFrom Addressとして使用しない
- HTMLへ利用者入力を出力するときはEscapeする
- 改行を表示する場合も、Escape後に `nl2br()` を適用する
- Token、Password、SMTP認証情報、問い合わせ本文をLogへ出力しない
- 同期送信とQueue送信を機能単位で明示し、Queueを使用する場合はWorker、Retry、失敗Job、個人情報の保持を併せて設計する
- 複数メールを送信する場合は、送信順序、受付成功条件、部分的失敗、再送、重複防止を決める
- Notification、宛先、件名、Template、正常・失敗経路をFeature Testで確認する

この方針は維持・採用する実装規約です。問い合わせ実装との不一致は本書の既知課題、認証メールとの不一致は `specifications/backend/laravel/authentication.md` の既知課題として管理します。

## 11. 個人情報とSession

問い合わせ入力には、氏名、会社名、メールアドレス、電話番号、URL、問い合わせ本文が含まれます。確認画面のため、これらをDatabase Sessionへ一時保存します。

現在の `.env.example` は `SESSION_DRIVER=database`、`SESSION_ENCRYPT=false`、`SESSION_LIFETIME=120` です。Session Payloadは暗号化されず、Sessionの有効期間中はDBから参照できる状態です。

送信成功時には入力Sessionを削除しますが、入力後に離脱した場合や送信失敗時はSessionの有効期限まで残る可能性があります。問い合わせ内容をアプリケーション固有テーブルへ恒久保存する処理はありません。

個人情報をSessionへ保存する場合は、必要な検証済み項目だけに限定し、保持期間、暗号化、削除条件、Logへの出力をバリデーション・セキュリティ仕様でも確認します。

## 12. 既知の課題

以下は本書作成時に確認済みの問い合わせ・メール実装課題です。改善後は本文を現在仕様へ更新し、解決済みIssueをこの一覧から削除します。

| Issue | 現在の課題 |
| --- | --- |
| [#23](https://github.com/DesignSupply/startify-app/issues/23) | 問い合わせ送信が未検証のPOST値を優先し、Sessionなしの直接送信、Hidden Field改ざん、連続・二重送信が可能 |
| [#24](https://github.com/DesignSupply/startify-app/issues/24) | 管理者宛先がコードへ固定され、2通の部分的成功、再送、失敗Log、同期・Queue方針が整理されておらず、`MAIL_ENCRYPTION` と `MAIL_SCHEME` の設定名も一致しない |

次の項目も現在実装の制約です。関連領域の仕様作成または実装変更時に再評価します。

- Database Sessionへ問い合わせの個人情報を暗号化せず一時保存する
- PHP ImageのMailpit Sendmail設定とSMTP Serviceが併存する
- 問い合わせ機能とメール配送のFeature Testがない

## 13. 検証

Docker環境起動後、`server/` でLaravelの自動テストとRoute一覧を確認します。

```bash
make laravel-test
```

```bash
make laravel-route
```

開発環境でメール送信を確認するときは、問い合わせまたは認証メールを操作し、Mailpit Web UIを確認します。

```text
http://localhost:8025
```

現在、問い合わせ入力、確認、送信、完了、宛先、Template、送信失敗を直接検証するFeature Testはありません。外部SMTPや実在アドレスへの送信を自動テストの前提にせず、LaravelのNotification Fakeなどを使用します。

## 14. 移行元資料

本書は、次の既存資料から設計意図を抽出し、現在のController、Form Request、Notification、Blade、Mail設定、Docker構成と照合して再構成しています。

- `specifications/backend/laravel/TASK_011.md`
- `.cursor/rules/app-overview.mdc`
- `.cursor/rules/dev-backend.mdc`
- `.cursor/rules/env-overview.mdc`

これらはドキュメント移行が完了するまで設計意図の確認に使用しますが、問い合わせとメールの現在仕様としては本書と現在の実装を優先します。
