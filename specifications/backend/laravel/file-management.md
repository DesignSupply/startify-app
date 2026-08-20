---
title: Laravelアプリケーション ファイル管理仕様
status: current
last_updated: 2026-08-20
related_paths:
  - backend/laravel/app/Helpers/UploadedFileHelper.php
  - backend/laravel/app/Http/Controllers/AdminFilesController.php
  - backend/laravel/app/Http/Requests/FileUploadRequest.php
  - backend/laravel/app/Http/Requests/FileUpdateRequest.php
  - backend/laravel/app/Models/UploadedFile.php
  - backend/laravel/app/Services/UploadedFileService.php
  - backend/laravel/config/filesystems.php
  - backend/laravel/database/migrations/
  - backend/laravel/resources/views/pages/admin/files/
  - backend/laravel/routes/web.php
  - backend/laravel/tests/
  - server/docker/nginx/nginx.conf
  - server/docker/php/Dockerfile
  - server/docker/php/php.ini
---

# Laravelアプリケーション ファイル管理仕様

Startify-AppのLaravelアプリケーションが提供する、管理者向けファイルアップロード、Metadata管理、画像プレビュー、ダウンロード、削除の現在仕様と維持する実装規約を定義します。

本書はファイル実体とMetadataの管理を扱います。画面とRouteの横断的な索引は `specifications/backend/laravel/screens-and-features.md`、`uploaded_files` テーブルの物理Schemaは `specifications/backend/laravel/database.md`、管理者Session認証は `specifications/backend/laravel/authentication.md` を参照してください。

## 1. 機能とアクセス境界

ファイル管理はLaravel MPAの管理者専用機能です。すべてのRouteは `auth:admin` Middleware内にあり、一般ユーザー向け画面およびJSON APIはありません。POST FormはBladeの `@csrf` を使用し、Laravel標準のCSRF検証を受けます。

| Method / Path | Route Name | Controller Action | 用途 |
| --- | --- | --- | --- |
| `GET /admin/files` | `admin.files.index` | `index` | 一覧画面 |
| `GET /admin/files/create` | `admin.files.create` | `create` | アップロード画面 |
| `POST /admin/files/store` | `admin.files.store` | `store` | アップロード処理 |
| `GET /admin/files/{id}` | `admin.files.show` | `show` | 詳細画面 |
| `GET /admin/files/{id}/edit` | `admin.files.edit` | `edit` | 説明編集・削除画面 |
| `POST /admin/files/{id}/update` | `admin.files.update` | `update` | 説明更新処理 |
| `POST /admin/files/{id}/delete` | `admin.files.destroy` | `destroy` | 物理削除処理 |
| `GET /admin/files/{id}/download` | `admin.files.download` | `download` | Download Response |

現在、ファイル管理Routeの `{id}` には `whereNumber('id')` を指定していません。Controllerは `findOrFail()` で対象を取得し、存在しないIDは404にします。

## 2. ファイルとMetadata

Storage上のファイル実体と、`uploaded_files` テーブルのMetadataを組み合わせて管理します。

| Field | 現在の値・用途 |
| --- | --- |
| `filename` | Clientから受け取った元ファイル名。画面表示とDownload時の名前に使用 |
| `stored_filename` | UUIDと元ファイル名の拡張子を組み合わせた保存名 |
| `file_path` | `uploads` Disk内の相対Path |
| `mime_type` | Server側の `getMimeType()` で検出したMIME Type |
| `file_size` | Byte単位のサイズ |
| `file_extension` | Clientから受け取った元ファイル名の拡張子 |
| `uploaded_by` | Uploadを実行した `admin_users.id` |
| `description` | 任意の説明文 |

`UploadedFile::uploader()` は `uploaded_by` で `AdminUser` にBelongs Toします。管理者削除時の外部キー動作はUpdate / DeleteともにRestrictです。ファイルは論理削除せず、削除操作でStorage上の実体とDBレコードを物理削除します。

`filename` はDB上で `varchar(255)` ですが、現在の `FileUploadRequest` は元ファイル名の長さを明示的に検証しません。通常より長い元ファイル名によってStorage保存後のDB登録が失敗する可能性があるため、Issue #29でファイル名と拡張子を正規化するときに長さ制限も併せて検討します。DB登録失敗時にStorageへ残るファイルの補償処理はIssue #28で管理します。

ファイルサイズ表示は `human_file_size` Accessorから `UploadedFileHelper::formatBytes()` を呼び出し、B、KB、MB、GB、TBへ変換します。

## 3. アップロードValidation

`FileUploadRequest` は次の入力を検証します。

| Field | 現在のRule | 必須 |
| --- | --- | --- |
| `upload_file` | File、最大10,240KB、許可形式を `mimes` で検証 | 必須 |
| `description` | 文字列、最大1,000文字 | 任意 |

現在許可されている形式は次のとおりです。

| 種別 | 拡張子 |
| --- | --- |
| 画像 | `jpg`、`jpeg`、`png`、`gif`、`webp` |
| 文書・表計算・プレゼンテーション | `pdf`、`csv`、`doc`、`docx`、`xls`、`xlsx`、`ppt`、`pptx` |
| 動画 | `mp4` |

現在のValidationはTXTを許可していません。許可形式を変更するときはValidation、形式判定、画像処理環境、テストを同時に更新します。

Laravelの `mimes` Ruleは、元ファイル名ではなく、ファイル内容から推測された拡張子を検証します。一方、現在の保存処理は `getClientOriginalExtension()` を使用します。この不一致はIssue #29で管理します。

`FileUploadRequest` はファイルと説明に共通の `max` Error Messageを使用します。説明が1,000文字を超えた場合も「1,000 KB以下」と表示されるため、Validation自体は機能しますが、説明の単位とError Messageが一致していません。

## 4. Storage構成

ファイル実体は `uploads` Diskへ保存します。

| 設定 | 現在値 |
| --- | --- |
| Driver | `local` |
| Root | `storage/app/private/uploads/` |
| Visibility | `private` |
| `throw` | `false` |
| `report` | `false` |

`uploads` DiskはPublic Storage Linkの対象外です。Nginxから直接配信せず、認証済みのDownload Routeを経由して取得します。`FILESYSTEM_DISK` が別のDiskを指していても、ファイル管理は明示的に `uploads` Diskを使用します。

一方、Defaultの `local` Diskは `storage/app/private/` をRootとして `serve: true` を設定しているため、Laravelは署名付き配信用の `GET /storage/{path}` Routeを自動登録します。`uploads` のRootはその配下にありますが、現在のファイル管理は `local` Diskの一時URLを生成せず、`admin.files.download` だけを使用します。将来署名付きURLを導入する場合は、管理者認証を経由する現在の配信方針と、有効期限付きURLを所持する利用者へ許可する範囲を明示します。

現在の受信上限は、Nginxの `client_max_body_size`、PHPの `upload_max_filesize` と `post_max_size` が64MB、LaravelのFile Validationが10MBです。ファイル管理では最も小さいLaravelの10MB上限が適用されます。

## 5. アップロード処理

`AdminFilesController::store()` は次の順序で処理します。

1. `FileUploadRequest` で入力を検証する
2. 元ファイル名、Client由来の拡張子、Server検出MIME Type、Byte数を取得する
3. UUIDとClient由来の拡張子から保存名を生成する
4. `uploads` Diskへファイルを保存する
5. 認証中の管理者IDとMetadataを `uploaded_files` へ登録する
6. 詳細画面へRedirectし、完了メッセージを表示する

現在はStorage保存とDB登録をControllerが直接実行し、`storeAs()` の失敗確認やDB登録失敗時の補償削除を行いません。StorageとDBの整合性およびServiceの責務整理はIssue #28とIssue #11で管理します。

## 6. 一覧・詳細・更新

### 6.1 一覧

一覧はUploaderをEager Loadingし、作成日時の降順ですべてのファイルを取得します。元ファイル名、表示用ファイルサイズ、アップロード日時、アップロード管理者名を表示します。現在はPaginationを行いません。

### 6.2 詳細

詳細画面は元ファイル名、表示用ファイルサイズ、MIME Type、アップロード日時、アップロード管理者名、説明、プレビューを表示し、編集とダウンロードへの導線を提供します。

Bladeでは元ファイル名、説明、管理者名を通常のEscape出力で表示します。

### 6.3 更新

編集画面ではファイル実体や元ファイル名を変更せず、説明だけを更新できます。`FileUpdateRequest` は説明を任意の文字列、最大1,000文字として検証します。更新成功後は詳細画面へRedirectします。

## 7. 画像プレビュー

`UploadedFileHelper::isImageExtension()` は、`jpg`、`jpeg`、`png`、`gif`、`svg`、`webp` を画像として判定します。SVGは現在のアップロード許可形式には含まれません。

画像と判定された場合、詳細画面の表示時に次の処理を行います。

1. Modelの `generateThumbnail()` から `UploadedFileService` を呼び出す
2. Storageから元ファイル全体を読み込む
3. Intervention ImageのGD Driverで画像を読み込む
4. Aspect Ratioを維持して幅300px以下へ縮小する
5. 生成結果をData URIへBase64 EncodeしてBladeへ渡す

サムネイルは永続保存せず、詳細画面を表示するたびに生成します。生成に失敗した場合はServiceが `null` を返し、画面へ失敗メッセージを表示します。現在は失敗原因をログへ記録しません。

現在のDocker環境のGDはJPEG、PNG、GIFに対応していますが、WebPに対応していません。また、画像の幅・高さにValidation上限がなく、PHPの `memory_limit` は256MBです。対応形式、画像寸法、リソース制限、ログ、テストの改善はIssue #30で管理します。

`UploadedFileHelper::isPreviewableExtension()` は画像に加えてTXT、Markdown、CSV、PDFをPreview可能と判定しますが、現在のModel・画面では画像以外のPreview表示に使用していません。

## 8. ダウンロード

Download処理はDBレコードを取得し、`uploads` Diskに `file_path` が存在することを確認します。実体がない場合は404を返します。

実体がある場合はLaravel Filesystemの `download()` を使用し、元ファイル名をDownload名としたAttachment Responseを返します。LaravelはFramework内でContent-DispositionとASCII Fallback名を生成します。Storage上のPathを利用者へ公開しません。

## 9. 削除

削除画面はBrowserの確認Dialogを表示し、POST Formで削除処理を実行します。現在のControllerはStorage上に実体があれば削除を試み、その後にDBレコードを削除します。削除成功後は一覧画面へRedirectします。

`UploadedFileService::deleteFile()` にもStorageとDBを削除する処理がありますが、現在のControllerは使用していません。ControllerとServiceの処理は重複し、Storage削除の戻り値も確認していません。改善方針はIssue #28で管理します。

## 10. 実装時の共通方針

ファイル管理を変更するときは、次を基本とします。

- アップロード、更新、削除、ダウンロードは管理者認証の内側へ置き、変更操作はCSRF検証を必須にする
- 利用者由来の元ファイル名や拡張子をStorage上の信頼済みPathとして使用しない
- 許可形式、Server検出MIME Type、保存拡張子、画像処理環境を整合させる
- ファイル実体は非公開Storageへ保存し、認証済みResponseを介して配信する
- StorageとDBは同一TransactionでRollbackできないため、失敗検知と補償処理を設計する
- 画像は容量だけでなく展開後の寸法とメモリ使用量を考慮する
- ファイル内容、Base64 Data、認証情報をログへ出力しない
- 正常系に加え、保存失敗、DB失敗、破損画像、未対応形式、削除失敗をテストする

これらは維持・採用する実装規約です。現在実装との不一致は、次の既知Issueで管理します。

## 11. 既知の課題

以下は本書作成時に確認済みのファイル管理課題です。改善後は本文を現在仕様へ更新し、解決済みIssueをこの一覧から削除します。

| Issue | 現在の課題 |
| --- | --- |
| [#11](https://github.com/DesignSupply/startify-app/issues/11) | Controllerと `UploadedFileService` の責務、ファイル操作の再利用・テスト境界が整理されていない |
| [#28](https://github.com/DesignSupply/startify-app/issues/28) | Storage保存・削除とDB登録・削除の失敗時に、ファイル実体とMetadataが不整合になる可能性がある |
| [#29](https://github.com/DesignSupply/startify-app/issues/29) | Validationで確認する形式と保存拡張子の情報源が異なり、MIME Type・保存名・DB Metadataが一致しない可能性がある |
| [#30](https://github.com/DesignSupply/startify-app/issues/30) | WebP許可とGD対応形式が一致せず、画像寸法制限、サムネイル失敗ログ、画像処理テストが未整備である |

次の項目も現在実装の制約です。関連領域の仕様作成または実装変更時に再評価します。

- ファイル管理用のJSON APIがない
- ファイル管理のFeature Test・Unit Testがない
- 一覧にPaginationがない
- 画像以外のPreview可能判定を画面で使用していない
- ファイル管理Routeの数値ID制約が統一されていない
- 元ファイル名の長さを明示的に制限していない
- アップロード時の説明文Length ErrorがKB単位で表示される

## 12. 検証

Docker環境起動後、`server/` でコンテナー状態、Route、Migration、GDの対応形式、テストを確認します。

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
docker compose exec app php -r "print_r(gd_info());"
```

```bash
make laravel-test
```

現在の自動テストは基本Test 2件だけで、ファイルのアップロード、表示、更新、プレビュー、ダウンロード、削除、認証境界を直接検証しません。調査・レビューだけの場合は、Storageへのファイル保存、DBレコードの作成・更新・削除を行いません。
