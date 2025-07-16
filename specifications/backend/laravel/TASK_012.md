---
title: バックエンド実装タスクリスト（Laravel）:ファイルアップロード機能の実装
id: laravel_task_012
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:ファイルアップロード機能の実装

各種ファイルのアップロード機能を実装します。

---

## 1. ファイルアップロードテーブルのマイグレーションファイルを作成

マイグレーションファイルを作成、マイグレーションを実行しテーブルを作成します。

- マイグレーション
  - パス: `/backend/laravel/database/migrations/[YYYY_MM_DD_HHMMSS]_create_uploaded_files_table.php`
  - クラス: `CreateUploadedFilesTable`
  - テーブル: `uploaded_files`
  - カラム
    - `id` / big integer / primary key / auto increment / unsigned
    - `filename` / string / ※オリジナルファイル名
    - `stored_filename` / string / ※保存時ファイル名
    - `file_path` / string
    - `mime_type` / string
    - `file_size` / big integer / unsigned
    - `file_extension` / string
    - `uploaded_by` / big integer / unsigned / ※アップロードユーザーのid（admin_usersテーブルのid）
    - `description` / text / nullable
    - `created_at` / timestamp / nullable
    - `updated_at` / timestamp / nullable
  - 外部キー制約
    - `uploaded_by` → admin_users.id
  - インデックス制約
    - `uploaded_by`, `created_at` / 複合インデックス

---

## 2. ファイルアップロードのモデル・ヘルパー・サービスを作成

ファイルアップロードのモデル・ヘルパー・サービスを作成します。

- モデル
  - モデル: `UploadedFile`
  - パス: `/backend/laravel/app/Models/UploadedFile.php`
  - クラス: `UploadedFile`
  - 機能仕様
    - データベースとの関連処理（リレーション）
    - アクセサー（`getHumanFileSizeAttribute`）
    - 簡単な状態判定メソッド（`isImage`、`isPreviewable`）
    - サービスクラスの呼び出し用のメソッド（`generateThumbnail`）

- ヘルパー
  - クラス: `UploadedFileHelper`
  - パス: `/backend/laravel/app/Helpers/UploadedFileHelper.php`
  - 機能仕様
    - ファイルサイズのフォーマット処理
    - ファイル拡張子の判定処理（画像、プレビュー可能）
    - 汎用的なファイル関連のヘルパー処理

- サービス
  - クラス: `UploadedFileService`
  - パス: `/backend/laravel/app/Services/UploadedFileService.php`
  - 機能仕様
    - 画像サムネイル生成処理
    - ファイル削除処理（ストレージとデータベースの両方）
    - 複雑なファイル操作に関するビジネスロジック

ファイルアップロードのモデル作成後、`config/filesystems.php` にある `uploads` の設定で、ファイルの保存先が `storage/app/private/uploads/` になるよう設定し、ファイルへの直接アクセスを制限します。

---

## 3. ファイルアップロード画面のビューを作成

ファイルアップロード機能に必要となるページのビューを作成します。

- ビュー（一覧画面）
  - パス: `/backend/laravel/resources/views/pages/admin/files/index.blade.php`
  - 機能仕様
    - リスト形式でファイル名、ファイルサイズ、アップロード日時、アップロードユーザー名を表示
    - 各リストアイテムのファイル名の項目に、詳細画面へのリンクを設定
    - アップロード画面へのリンクを設置
- ビュー（登録画面）
  - パス: `/backend/laravel/resources/views/pages/admin/files/create.blade.php`
  - 機能仕様
    - ファイル選択インプット、ファイル説明文を入力するテキストエリア、サブミットボタンを設けたフォームを表示
    - アップロードファイル一覧画面へのリンクを設置
- ビュー（詳細画面）
  - パス: `/backend/laravel/resources/views/pages/admin/files/show.blade.php`
  - 機能仕様
    - 画像ファイルの場合は300pxのサムネイルプレビューを表示
    - 編集画面へのリンクを設置
    - アップロードファイル一覧画面へのリンクを設置
- ビュー（編集画面）
  - パス: `/backend/laravel/resources/views/pages/admin/files/edit.blade.php`
  - 機能仕様
    - ファイル説明文を更新するテキストエリア、ファイルを削除するボタンを設けたフォームを表示
    - 詳細画面へのリンクを設置
    - フォームのインプット要素には登録されている値が初期値として設定されている

---

## 4. ファイルアップロードのルーティングを作成

ファイルアップロード機能で使用するルーティングを作成します。今回はすべて管理者認証ルーティング配下で管理されるものとします。

- ルーティング
  - 一覧（画面表示）
    - パス: `/admin/files`
    - メソッド: `GET`
    - ルート名: `admin.files.index`
  - 登録（画面表示）
    - パス: `/admin/files/create`
    - メソッド: `GET`
    - ルート名: `admin.files.create`
  - 登録（フォーム送信）
    - パス: `/admin/files/store`
    - メソッド: `POST`
    - ルート名: `admin.files.store`
  - 詳細（画面表示）
    - パス: `/admin/files/{id}`
    - メソッド: `GET`
    - ルート名: `admin.files.show`
  - 編集（画面表示）
    - パス: `/admin/files/{id}/edit`
    - メソッド: `GET`
    - ルート名: `admin.files.edit`
  - 編集（フォーム送信・更新）
    - パス: `/admin/files/{id}/update`
    - メソッド: `POST`
    - ルート名: `admin.files.update`
  - 編集（フォーム送信・削除）
    - パス: `/admin/files/{id}/delete`
    - メソッド: `POST`
    - ルート名: `admin.files.destroy`
  - ダウンロード
    - パス: `/admin/files/{id}/download`
    - メソッド: `GET`
    - ルート名: `admin.files.download`

---

## 5. ファイルアップロード用のリクエストクラスを作成

コンタクトフォーム用のリクエストクラスを作成します。

- リクエストクラス
  - クラス: `FileUploadRequest`
  - パス
    - アップロード用: `/backend/laravel/app/Http/Requests/FileUploadRequest.php`
    - 更新用: `/backend/laravel/app/Http/Requests/FileUpdateRequest.php`
  - バリデーション
    - ファイル
      - 許可するファイル形式
        - 画像: jpg、jpeg、png、gif、webp
        - 文書: pdf、txt、csv、doc、docx、xls、xlsx、ppt、pptx
        - 動画: mp4
      - ファイルサイズ
        - 上限: 10MB
      - その他
        - 拡張子偽装を防止する
    - 説明文
      - 最大1000文字

---

## 6. ファイルアップロード機能のコントローラーを作成

ファイルアップロード機能のコントローラーを作成します。

- コントローラー
  - クラス: `AdminFilesController`
  - パス: `/backend/laravel/app/Http/Controllers/AdminFilesController.php`
  - メソッド:
    - `index`
    - `create`
    - `store`
    - `show`
    - `edit`
    - `update`
    - `destroy`
    - `download`
  - 機能仕様
    - ファイルアップロード完了後は該当ファイルの詳細画面へリダイレクトさせます
    - ファイルの削除完了後は一覧画面へリダイレクトさせます
    - ファイルの更新完了時には更新完了のメッセージを表示させます
    - 管理者ログイン未認証の場合には `/admin` へリダイレクトさせます

---

## 7. ファイルアップロード機能のテスト

---
