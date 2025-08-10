# Startify-App

Startify-Appは、AI駆動開発に対応できるように設計されたウェブアプリケーション・ウェブサイトの開発環境です。DockerやNode.jsの環境を使用して、バックエンドとフロントエンドに対応する、さまざまなフレームワークを用いた開発環境を構築します。

フレームワークごとにウェブプリケーションに必要となる基本的な機能を実装したコードが初期状態で備わっていますが、開発環境の構築手順やアプリケーションの概要を記載した各種手順書などを活用することで、新たな機能の実装や開発環境の拡張をAIを活用したコーディングによって効率的に進めることができます。

## 開発環境構築・実装済み機能

Startify-Appには、Webアプリケーション開発およびWebサイト制作に必要なローカル開発環境と基本的な機能がデモとして実装されています。これらの機能をベースに、AIを活用した開発を効率的に進めることができます。

- Docker
  - Nginx
  - PHP
  - MySQL(MariaDB)
  - Mailpit
- Laravel
  - 管理者権限のユーザーロール
  - 自動返信メール対応のコンタクトフォーム
  - ログイン認証
  - パスワードリセット
  - ユーザー情報編集・更新
  - 一般ユーザー新規登録
  - 【管理者ユーザー向け】サムネイル出力対応のファイルアップロード
  - 【管理者ユーザー向け】一般ユーザー管理（編集・削除・復元）
- WordPress
  - クラシックテーマ
    - カスタム投稿・カスタム分類機能追加
    - カスタマイズが容易な各種ページテンプレート
    - 非同期での投稿データ取得
    - WP REST APIの独自エンドポイント
    - その他汎用的に使えるコンポーネント
- Next.js
  - 各種リンター・フォーマッター
  - 環境変数の使用
  - TailwindCSSの使用
  - Sassのコンパイル
  - UIコンポーネント最適化
  - メタデータ最適化
  - フォント最適化
  - グローバルステート管理
  - サイトマップ出力
  - PWA対応
- Vite
  - 各種リンター・フォーマッター
  - React、Vue.jsの使用
  - TailwindCSSの使用
  - Sassのコンパイル
  - Pug、Handlebarsのコンパイル
  - マルチページビルト対応
  - メタデータの一元管理
  - 各種ライブラリのサンプルコード

## 導入

### 1. Docker環境構築

用意された各種Dockerfileを使用してDockerコンテナーを起動します。

```bash
cd ./server

# ビルド
make build

# コンテナーの起動
make up
```

### 2. Laravelのインストール・セットアップ

Dockerコンテナーが起動後、Laravelのインストール・セットアップを行います。

```bash
cd ./server

# Composerインストール
make laravel-install

# キー生成
make laravel-keygen

# ストレージリンク設定
make laravel-storage-link
make laravel-storage-link-change

# マイグレーション
make laravel-migrate
```

http://localhost/ にアクセスすることでLaravelのアプリケーションフロントページが表示されます。

### 3. WordPressのインストール・セットアップ

Dockerコンテナーが起動後、WordPressのインストール・セットアップを行います。

```bash
cd ./server

# WordPressのインストール・セットアップ
make wp-setup

# シンボリックリンク設定
make wp-symlinks
```

http://cms.localhost/ にアクセスすることでWordPressのサイトトップページが表示されます。

### 4. Next.jsのインストール（ローカル環境）

ローカル環境にNode.jsをインストール後、各種モジュールのインストールを行います。

```bash
cd ./frontend/next

# インストール
npm install

# ローカルサーバー起動
npm run dev
```

http://localhost:3000/ にアクセスすることでNext.jsのアプリケーショントップページが表示されます。

---

## アプリケーション要件

- Docker: ^27.10.0
- docker-compose: ^2.31.0
- Docker Desktop: ^4.0
- Node.js: ^22.11.0
- npm: ^10.8.2

---

## AI駆動開発

本環境では、Cursorを使用したAI駆動開発を想定した仕様書ドキュメントを収録しています。 `/specifications/` ディレクトリ配下にマークダウン形式で、機能実装のタスクを記載しており、それらをコンテキストとして読み込んだプロンプトを実行することで、より再現性の高い機能実装が可能になります。

---

## 備考

- 開発環境概要については `/.cursor/rules/env-overview.mdc` のファイルを参照ください。
- 開発環境で使用される変数は `/server/.env` で管理できます。
- この開発環境では、Laravel、WordPressのアプリケーションディレクトリがドキュメントルート外にインストールされる形になります。
