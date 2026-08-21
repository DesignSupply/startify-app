---
title: Laravelアプリケーション ユーザー・プロフィール管理仕様
status: current
last_updated: 2026-08-21
related_paths:
  - backend/laravel/app/Http/Controllers/ProfileController.php
  - backend/laravel/app/Http/Controllers/AdminProfileController.php
  - backend/laravel/app/Http/Controllers/AdminUsersController.php
  - backend/laravel/app/Models/User.php
  - backend/laravel/app/Models/AdminUser.php
  - backend/laravel/database/migrations/
  - backend/laravel/resources/views/pages/profile/
  - backend/laravel/resources/views/pages/admin/profile/
  - backend/laravel/resources/views/pages/admin/users/
  - backend/laravel/routes/web.php
  - backend/laravel/tests/
---

# Laravelアプリケーション ユーザー・プロフィール管理仕様

Startify-AppのLaravel MPAが提供する、一般ユーザー本人のプロフィール、管理者本人のプロフィール、管理者による一般ユーザー管理の現在仕様と、機能変更時に維持する方針を定義します。

本書はユーザー情報の表示・更新、本人確認、管理操作、論理削除・復元を扱います。GuardとSession、ログイン、新規登録、パスワード再設定、JWTの詳細は `specifications/backend/laravel/authentication.md`、画面とRouteの索引は `specifications/backend/laravel/screens-and-features.md`、物理Schemaは `specifications/backend/laravel/database.md`、横断的な入力検証と認可方針は `specifications/backend/laravel/validation-and-security.md` を正本とします。

## 1. 利用者区分と機能境界

現在はRole・PermissionをDBで管理するRBACではなく、一般ユーザーと管理者を別のModel、Provider、Session Guardで管理します。

| 利用者区分 | Model | Guard / Middleware | 本書で扱う主な操作 |
| --- | --- | --- | --- |
| 一般ユーザー | `User` | `web` / `auth` | 一般ユーザープロフィールの表示・編集・更新 |
| 管理者 | `AdminUser` | `admin` / `auth:admin` | 管理者プロフィールの表示・編集・更新 |
| 管理者 | `AdminUser` | `admin` / `auth:admin` | 一般ユーザーの一覧・詳細・更新・論理削除・復元 |

認証済みかどうかはRoute Middlewareで確認し、本人判定および編集・更新の本人確認はプロフィールControllerで行います。管理者による一般ユーザー管理は `auth:admin` の内側にあり、一般ユーザーは利用できません。

## 2. Routeとアクセス境界

### 2.1 一般ユーザープロフィール

| Method / Path | Route Name | Controller Action | 現在の用途 |
| --- | --- | --- | --- |
| `GET /profile` | `profile.redirect` | `ProfileController::redirect` | ログイン中ユーザーのプロフィールへRedirect |
| `GET /profile/{id}` | `profile` | `ProfileController::index` | 指定した一般ユーザーのプロフィール表示 |
| `GET /profile/{id}/edit` | `profile.edit` | `ProfileController::edit` | 本人の編集画面 |
| `POST /profile/{id}/update` | `profile.update` | `ProfileController::update` | 本人の更新処理 |

すべて `auth` Middleware内にあります。現在、`{id}` に `whereNumber('id')` は指定していません。

### 2.2 管理者プロフィール

| Method / Path | Route Name | Controller Action | 現在の用途 |
| --- | --- | --- | --- |
| `GET /admin/profile` | `admin.profile.redirect` | `AdminProfileController::redirect` | ログイン中管理者のプロフィールへRedirect |
| `GET /admin/profile/{id}` | `admin.profile` | `AdminProfileController::index` | 指定した管理者のプロフィール表示 |
| `GET /admin/profile/{id}/edit` | `admin.profile.edit` | `AdminProfileController::edit` | 本人の編集画面 |
| `POST /admin/profile/{id}/update` | `admin.profile.update` | `AdminProfileController::update` | 本人の更新処理 |

すべて `auth:admin` Middleware内にあります。現在、`{id}` に `whereNumber('id')` は指定していません。

### 2.3 管理者による一般ユーザー管理

| Method / Path | Route Name | Controller Action | 用途 |
| --- | --- | --- | --- |
| `GET /admin/users` | `admin.users.index` | `AdminUsersController::index` | 一覧 |
| `GET /admin/users/{id}` | `admin.users.show` | `AdminUsersController::show` | 詳細 |
| `GET /admin/users/{id}/edit` | `admin.users.edit` | `AdminUsersController::edit` | 編集・削除・復元画面 |
| `POST /admin/users/{id}/update` | `admin.users.update` | `AdminUsersController::update` | 更新 |
| `POST /admin/users/{id}/delete` | `admin.users.destroy` | `AdminUsersController::destroy` | 論理削除 |
| `POST /admin/users/{id}/restore` | `admin.users.restore` | `AdminUsersController::restore` | 復元 |

すべて `auth:admin` Middleware内にあり、`{id}` には `whereNumber('id')` を指定します。POST FormはBladeの `@csrf` を使用します。

## 3. 一般ユーザープロフィール

### 3.1 表示

`ProfileController::index()` はRoute ParameterのIDを文字列として厳密に比較するため、`CAST(id AS CHAR) = ?` のBindingを使用してUserを取得します。存在しない場合は404を返します。

現在はログイン中ユーザーと対象ユーザーが異なる場合も表示を許可し、Viewの `isOwn` で表示項目を切り替えます。

| 表示項目・操作 | 本人 | 本人以外 |
| --- | --- | --- |
| 名前 | 表示 | 表示 |
| メールアドレス | 表示 | 非表示 |
| 登録日 | 表示 | 非表示 |
| 最終更新日 | 表示 | 非表示 |
| 編集画面へのLink | 表示 | 非表示 |

画面上に他ユーザーへの導線はありませんが、認証済み一般ユーザーは `/profile/{id}` へ直接アクセスできます。取得条件に `active()` Scopeを使用しないため、論理削除済みUserも名前の表示対象です。この認可境界の改善はIssue #35で管理します。

### 3.2 編集と更新

編集・更新では、ログイン中ユーザーと対象UserのIDが一致することをControllerで確認します。本人以外の場合は、現在は指定された対象UserのプロフィールへRedirectします。

本人は現在、1つの画面と更新処理で次を変更できます。

- 名前
- メールアドレス
- パスワード

メールアドレスは入力値をそのまま `users.email` へ保存し、変更時も `email_verified_at` を更新しません。そのため、変更前のメールアドレスに対する確認日時が残ります。

更新成功後は本人のプロフィールへRedirectし、`プロフィールを更新しました` をFlash Messageとして表示します。

通常プロフィールとメールアドレス・パスワードの変更フロー分離、現在のパスワード確認、新しいメールアドレスの所有確認はIssue #32で管理します。

## 4. 管理者プロフィール

### 4.1 表示

`AdminProfileController::index()` は一般ユーザープロフィールと同じく、`CAST(id AS CHAR) = ?` のBindingでAdminUserを取得し、存在しない場合は404を返します。

現在はログイン中管理者と対象管理者が異なる場合も表示を許可し、Viewの `isOwn` で表示項目を切り替えます。

| 表示項目・操作 | 本人 | 本人以外 |
| --- | --- | --- |
| 名前 | 表示 | 表示 |
| メールアドレス | 表示 | 非表示 |
| 登録日 | 表示 | 非表示 |
| 最終更新日 | 表示 | 非表示 |
| 編集画面へのLink | 表示 | 非表示 |

管理者プロフィールも本人専用に変更する方針はIssue #35で管理します。

### 4.2 編集と更新

編集・更新では、ログイン中管理者と対象AdminUserのIDが一致することをControllerで確認します。本人以外の場合は、現在は指定された対象管理者のプロフィールへRedirectします。

管理者本人は現在、1つの画面と更新処理で名前、メールアドレス、パスワードを変更できます。メールアドレスを変更しても `email_verified_at` は更新しません。更新成功後は本人の管理者プロフィールへRedirectし、`管理者プロフィールを更新しました` をFlash Messageとして表示します。

今後は名前だけを通常プロフィールの編集対象とし、管理者メールアドレスは表示専用のシステム管理項目、パスワードは現在のパスワードを確認する専用画面で扱います。この未実装方針はIssue #32で管理します。

## 5. 管理者による一般ユーザー管理

### 5.1 一覧と詳細

一覧はUserを `created_at` の降順で全件取得し、有効・論理削除済みの両方を表示します。現在はPaginationを使用しません。

| 画面 | 表示内容 |
| --- | --- |
| 一覧 | ID、名前、削除状態 |
| 詳細 | ID、名前、メールアドレス、作成日時、削除状態 |

対象Userが存在しない場合、詳細・編集・更新・削除・復元は `findOrFail()` により404を返します。

### 5.2 更新

管理者は一般ユーザーの名前、メールアドレス、パスワードを同じ更新処理で変更できます。パスワードは入力された場合だけHash化して更新します。

更新成功後は対象Userの詳細へRedirectし、`ユーザー情報を更新しました。` をFlash Messageとして表示します。

現在、管理者がメールアドレスを変更しても `email_verified_at` は更新されません。管理者による変更を運営の本人確認済み操作とみなし、変更時刻へ更新する方針はIssue #34で管理します。

管理者がパスワードやメールアドレスを変更した後の対象UserのDatabase SessionとRefresh Token失効はIssue #18で管理します。

## 6. 現在のValidation

プロフィール更新と管理者による一般ユーザー更新は、いずれもController内の `$request->validate()` を使用します。

| Field | 一般ユーザー本人 | 管理者本人 | 管理者による一般ユーザー更新 |
| --- | --- | --- | --- |
| `name` | 必須、文字列、最大255文字 | 必須、文字列、最大255文字 | 必須、文字列、最大255文字 |
| `email` | 必須、文字列、メール形式、本人を除くUnique | 必須、文字列、メール形式、本人を除くUnique | 必須、文字列、メール形式、対象Userを除くUnique |
| `password` | 任意、文字列、最小8文字、確認入力一致 | 任意、文字列、最小8文字、確認入力一致 | 任意、文字列、最小8文字、確認入力一致 |

現在、Passwordの最大長は指定していません。一般ユーザー本人と管理者本人の認証情報変更ValidationはIssue #32、変更後の認証状態はIssue #18で改善します。

## 7. 論理削除と復元

一般ユーザーはLaravelの `SoftDeletes` Traitではなく、`is_deleted` と `deleted_at` を明示的に更新します。

| 操作 | `is_deleted` | `deleted_at` |
| --- | --- | --- |
| 論理削除 | `true` | 現在時刻 |
| 復元 | `false` | `null` |

削除処理はDB Transaction内で、未削除の場合だけ状態を更新し、`sessions.user_id` が対象User IDと一致するDatabase Sessionを削除します。処理後は管理者の一般ユーザー一覧へRedirectし、完了Messageを表示します。

復元処理は削除済みの場合だけ状態を戻します。削除時・復元時は、操作した管理者IDと対象User IDをApplication Logへ記録します。

現在、論理削除時に対象UserのRefresh Tokenを失効しません。削除済みUserのAPI認証拒否とRefresh Token失効はIssue #15、SessionとToken失効の横断方針はIssue #18で管理します。

## 8. 認可と情報公開の方針

現在の認可はRoute MiddlewareとControllerのID一致確認で行い、PolicyとGateは使用していません。

機能変更時は次を維持します。

- 一般ユーザー用Routeは `auth`、管理者用Routeは `auth:admin` の内側に置く
- ViewのLinkや表示制御だけを認可として使用しない
- 表示、編集、更新それぞれのServer側認可を確認する
- Route Parameterを変更しても他人の情報を更新できないようにする
- 管理者による一般ユーザー管理と、管理者本人のプロフィール管理を区別する
- 論理削除済みUserを一般向け機能で扱う場合は、機能ごとに明示的に条件を定義する
- 個人情報と認証情報は、操作に必要な利用者だけへ表示する

プロフィールを本人専用に統一し、Route ID制約とFeature Testを追加する方針はIssue #35で管理します。

## 9. 認証情報変更と認証状態

メールアドレスはログインID、パスワードは認証秘密情報です。名前などの通常プロフィール情報と同じ更新処理に含める場合でも、変更後のSession、Refresh Token、メール確認状態への影響を確認します。

現在の認証状態への影響は、変更主体と対象により次のように異なります。

- 一般ユーザー本人がメールアドレス・パスワードを変更しても、本人の既存Database SessionとRefresh Tokenを明示的に失効しない
- 管理者本人がメールアドレス・パスワードを変更しても、管理者の既存Sessionを明示的に失効しない。管理者向けJWT認証とRefresh Tokenは存在しない
- 管理者が一般ユーザーのメールアドレス・パスワードを変更しても、対象Userの既存Database SessionとRefresh Tokenを明示的に失効しない

現在のパスワード確認、メール所有確認、管理者メールアドレスの固定化はIssue #32、失効方針はIssue #18、管理者による一般ユーザーのメール確認済み状態はIssue #34で管理します。

## 10. 実装時の共通方針

- UserとAdminUser、本人操作と管理者操作を明確に区別する
- 更新前に認証・認可とValidationを完了する
- メールアドレスのUnique制約をDBの最終保証として維持する
- Passwordは平文で保存・ログ出力せず、LaravelのHash機能でHash化する
- 認証情報が実際に変更された場合だけ、確認日時や認証状態へ必要な副作用を適用する
- 複数のDB更新を一体として扱う場合はTransaction境界を定義する
- SessionやRefresh Token失効処理はControllerへ重複させず、Issue #11のService分離方針と整合させる
- 管理操作のLogには管理者IDと対象User IDを記録し、メールアドレス、Password、Tokenは記録しない
- 現在仕様を変更した場合は、本書と認証、画面、DB、Validation・Security仕様を同じ変更単位で更新する

## 11. 既知の課題

以下は現在実装と確定済みの改善方針との差異です。改善後は本文を現在仕様へ更新し、解決済みIssueをこの一覧から削除します。

| Issue | 現在の課題 |
| --- | --- |
| [#11](https://github.com/DesignSupply/startify-app/issues/11) | ユーザー更新、Session・Token失効などのビジネスロジックがControllerへ集中している |
| [#15](https://github.com/DesignSupply/startify-app/issues/15) | 一般ユーザーの論理削除時にRefresh Tokenを失効せず、削除済みUserをJWT APIが拒否しない |
| [#18](https://github.com/DesignSupply/startify-app/issues/18) | ログアウト、本人・管理者による認証情報変更、パスワード再設定後のSession・Refresh Token失効方針が統一されていない |
| [#32](https://github.com/DesignSupply/startify-app/issues/32) | 通常プロフィールとメールアドレス・パスワード変更が分離されず、本人確認とメール所有確認が不足している |
| [#34](https://github.com/DesignSupply/startify-app/issues/34) | 管理者が一般ユーザーのメールアドレスを変更しても `email_verified_at` が変更前の値のまま残る |
| [#35](https://github.com/DesignSupply/startify-app/issues/35) | 一般・管理者プロフィールの表示を本人だけに制限せず、一般ユーザーでは論理削除済みUserも表示できる |

## 12. 検証

Docker環境起動後、`server/` でRouteと現在の自動テストを確認します。

```bash
make laravel-route
```

```bash
make laravel-test
```

現在、プロフィールと一般ユーザー管理を直接検証するFeature Testはありません。変更時は少なくとも次を確認します。

- 未認証、一般ユーザー、管理者ごとのアクセス境界
- 本人と本人以外の表示・編集・直接POST
- 正常値、不正形式、重複メールアドレス、Password確認不一致
- 存在しないIDと数値以外のID
- 論理削除・復元の状態と冪等性
- 削除・認証情報変更後のSessionとRefresh Token
- Flash Messageと成功・失敗時のRedirect
- Logへ秘密情報や不要な個人情報を出力していないこと

## 13. 関連仕様書

| 領域 | 正本 |
| --- | --- |
| Laravel全体の機能境界 | `specifications/backend/laravel/overview.md` |
| 画面、Route、画面遷移 | `specifications/backend/laravel/screens-and-features.md` |
| User、AdminUser、Session、Refresh TokenのSchema | `specifications/backend/laravel/database.md` |
| Guard、Session、ログイン、認証情報変更 | `specifications/backend/laravel/authentication.md` |
| Validation、認可、CSRF、Log | `specifications/backend/laravel/validation-and-security.md` |
| Controller、Service、Modelの責務 | `specifications/backend/laravel/architecture.md` |
