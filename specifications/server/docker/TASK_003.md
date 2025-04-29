---
title: Docker開発環境構築手順:WordPress環境構築
id: docker_task_003
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Docker開発環境構築手順：WordPress環境構築

---

## 1. 環境設定ファイルの作成

### 1.1. .gitignoreの作成

`/backend/wordpress/` に　`.gitignore` ファイルを作成します。下記の条件に合わせて最適な設定になるよう作成します。

- 下記のファイル・ディレクトリはGit管理下におかないものとする
  - WordPressのコアファイル
  - WordPressのテーマファイル
  - WordPressの設定ファイル
  - 環境変数ファイル（`.env.example` を除く）
  - その他ログファイル、キャッシュファイル、サーバー関連のファイル、OS固有のファイル

### 1.2. .envの追記

`/server/.env` にWordPress用のDocker環境変数定義を追加します。WordPressのインストールやセットアップにこれらの値を参照できるようにします。

```env
# CMS WordPress
# Database
WP_DATABASE=cms
WP_DATABASE_USERNAME=admin
WP_DATABASE_PASSWORD=secret
WP_DATABASE_CHARSET=utf8mb4
WP_TABLE_PREFIX=wp_
# Admin
WP_ADMIN_USERNAME=admin
WP_ADMIN_PASSWORD=password
WP_ADMIN_EMAIL=admin@example.com
# Site
WP_SITEURL=http://cms.localhost/wordpress
WP_HOME=http://cms.localhost
# Debug
WP_DEBUG=true
WP_DEBUG_LOG=true
WP_DEBUG_DISPLAY=false
```

---

## 2. WordPressのインストール・セットアップ

Dockerコンテナー内でWordPressのインストールを行います、WordPressインストール後にマウントされた `/backend/wordpress` ディレクトリ配下にソースコードが同期されていることを確認します。

```bash
cd /server
make wp-setup
```

---

## 3. エントリーポイント、シンポリンクリンクの作成

WordPressはドキュメントルート外にインストールされる構成のため、設定作業を進めます。

### 3.1. シンボリックリンクの作成

WordPressのインストールディレクトリ用のシンボリックリンクを、ドキュメントルート配下に作成します。

```bash
cd /server
make wp-symlinks
```

---

## 4. テスト・表示確認

- `http://cms.localhost` にアクセスして、WordPressのサイトトップページが表示されることを確認します。
- `http://cms.localhost/wordpress/wp-admin` にアクセスして、WordPressの管理画面にログインができることを確認します。
- WordPressの管理画面内で投稿の公開、編集や削除、プラグインのインストールやアップデートができるかを確認します。
