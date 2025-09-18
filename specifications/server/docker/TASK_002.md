---
title: Docker開発環境構築手順:Laravel環境構築
id: docker_task_002
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Docker開発環境構築手順：Laravel環境構築

---

## 1. Laravelプロジェクトの作成

Dockerコンテナー内でLaravelのインストールを行います、Laravelインストール後にマウントされた `/backend/laravel` ディレクトリ配下にソースコードが同期されていることを確認します。

```bash
cd ./server
make laravel-install
```

---

## 2. 環境変数ファイルの設定、キーの生成

Dockerで使用している環境変数を `/backend/laravel/.env` ファイルに反映し、キーを生成します。

```bash
cd ./server
make laravel-keygen
```

---

## 3. データベースの作成、マイグレーション

下記のコマンドでデータベースの作成、マイグレーションを行います。

```bash
cd ./server
make laravel-migrate
```

ローカル環境のデータベースにアクセスし、Laravelのマイグレーションによってテーブルが作成されていることを確認します。

---

## 4. ストレージのシンボリックリンク作成

ストレージのシンボリックリンクを作成します。

```bash
cd ./server
make laravel-storage-link
```

アプリケーションのルートディレクトリがドキュメントルートより上の階層にある場合にはシンボリックリンクの場所を変更します。

```bash
cd ./server
make laravel-storage-link-change
```

シンボリックリンクの変更後、下記の場所にストレージのシンボリックリンクが作成されているかを確認します。

- `/backend/_webroot/storage`

---

## 5. アプリケーションエントリーポイントファイルの作成

下記の場所にアプリケーションのエントリーポイントファイルを作成します、既存のLaravelのエントリーポイントファイルをコピーし、Laravelのプロジェクトルートを参照するように変更します。

- `/backend/_webroot/index.php`

---

## 6. テスト・表示確認

```bash
cd ./server
make laravel-test
```

- テストが実行され、テスト結果が表示されることを確認します。
- `http://localhost/` にアクセスして、Laravelのテストページが表示されることを確認します。

---
