# Startify-App

Startify-Appは、AI駆動開発に対応できるように設計されたウェブアプリケーション・ウェブサイトの開発環境です。DockerやNode.jsの環境を使用して、バックエンドとフロントエンドに対応する、さまざまなフレームワークを用いた開発環境を構築します。

フレームワークごとにウェブアプリケーションに必要となる基本的な機能を実装したコードが初期状態で備わっていますが、開発環境の構築手順やアプリケーションの概要を記載した各種手順書などを活用することで、新たな機能の実装や開発環境の拡張をAIを活用したコーディングによって効率的に進めることができます。

## 開発環境構築・実装済み機能

Startify-Appには、Webアプリケーション開発およびWebサイト制作に必要なローカル開発環境と基本的な機能がデモとして実装されています。これらの機能をベースに、AIを活用した開発を効率的に進めることができます。

- Docker
  - Nginx
    - SSL証明書対応 ※ホストOS側でインストール要
  - PHP
  - MariaDB
  - Mailpit
    - ウェブメール画面（localhost:8025）
- Laravel
  - 管理者権限のユーザーロール
  - 自動返信メール対応のコンタクトフォーム
  - ログイン認証
  - パスワードリセット
  - ユーザー情報編集・更新
  - 一般ユーザー新規登録
  - 汎用投稿一覧・詳細の閲覧機能
  - 【管理者ユーザー向け】
    - サムネイル出力対応のファイルアップロード
    - 一般ユーザー管理（編集・削除・復元）
    - 汎用投稿CMS機能（タイトル・本文テキスト投稿、タグ・カテゴリ設定）
  - 【API】
    - JWTログイン認証（一般ユーザー）
- WordPress
  - クラシックテーマ
    - カスタム投稿・カスタム分類機能追加
    - カスタマイズが容易な各種ページテンプレート
    - 非同期での投稿データ取得
    - WP REST APIの独自エンドポイント
    - その他汎用的に使えるコンポーネント
- Next.js
  - Static Export対応
  - Cloudflare Workers Static Assets向けのBuild・Preview・Deploy
  - ESLint、Stylelint、Markuplint、Prettierによる品質検証
  - TypeScript型チェックとVitestによる自動テスト
  - 環境変数のサンプル設定
  - Tailwind CSSの使用
  - Sassのコンパイル
  - UIコンポーネント最適化
  - メタデータ最適化（Metadata API）
  - JSON-LDによる構造化データ出力
  - フォント最適化（next/font）
  - Google Analytics・Google AdSense対応
  - サイトマップ出力
  - PWA対応
  - GitHub Actionsによる品質検証・Cloudflare Stagingデプロイ
  - 【サンプル機能一覧】
    - ユニットテスト（Vitest, React Testing Library）
    - フォーム処理・バリデーション（React Hook Form、Zod）
    - UIグローバルステート管理（Zustand）
    - 認証APIを使ったログインフォームと認証ルーティング（バックエンド：Laravel）
    - サーバー状態管理（TanStack Query）とZodによる取得データ検証
    - モックJSONを使った投稿一覧・詳細の動的ルーティングページ
- Vite
  - 各種リンター・フォーマッター
  - React、Vue.jsの使用
  - Tailwind CSSの使用
  - Sassのコンパイル
  - Pug、Handlebarsのコンパイル
  - マルチページBuild対応
  - メタデータの一元管理
  - 各種ライブラリのサンプルコード
  - WebGL（Three.js）サンプルコード
  - GLSLシェーダー対応
- Astro
  - 各種リンター・フォーマッター
  - React、Vue.jsの使用
  - Tailwind CSSの使用
  - Sassのコンパイル
  - マルチページBuild対応
  - メタデータの一元管理
  - Markdownのコンテンツ管理と動的ページ生成
  - 各種ライブラリのサンプルコード
  - WebGL（Three.js）サンプルコード
  - GLSLシェーダー対応
- デザイントークン・Storybook
  - YAMLによる共通デザイントークン管理・構造検証
  - React UIコンポーネントの表示・確認
  - Controls・Autodocs・Accessibility Addon対応
  - Vitest・Playwright ChromiumによるStorybook Browser Test
  - TypeScript型チェックとStorybook静的Build

## 導入

### 1. Docker環境構築

用意された各種Dockerfileを使用してDockerコンテナーを起動します。

最初に`server/.env.example`から`server/.env`を準備します。`server/.env`がすでに存在する場合は上書きせず、現在の設定を確認して使用してください。

次のmkcert導入コマンドは、macOSでHomebrewを使用する場合の手順です。LinuxやWindowsなど、その他の環境では[mkcert公式のInstallation手順](https://github.com/FiloSottile/mkcert#installation)にしたがってインストールしてください。

```bash
cd ./server
cp -n .env.example .env

# macOSで証明書が必要な場合にはホストOSへインストール
brew install mkcert
mkcert -install
cd docker/nginx
mkdir -p certs
cd certs
mkcert localhost cms.localhost testing.localhost api.localhost

# ビルド
cd ../../..
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

# シーダー実行
make laravel-seed
```

`make laravel-keygen`は`backend/laravel/.env`とAPP_KEYの初回生成用です。既存環境へ再実行すると現在の設定を上書きするため、すでに`.env`が存在する場合は実行しないでください。この改善は[Issue #38](https://github.com/DesignSupply/startify-app/issues/38)で管理しています。

Storage Link用の2コマンドも初回セットアップ時に限って実行します。[Issue #40](https://github.com/DesignSupply/startify-app/issues/40)が解決するまでは、既存Linkの修復や再作成を目的として再実行しないでください。詳細は[Dockerローカル開発環境 セットアップ・運用手順](specifications/server/docker/setup-and-operations.md)を参照してください。

https://localhost/ にアクセスすることでLaravelのアプリケーションフロントページが表示されます。

また、認証APIを使用する場合にはSSLに対応させるためキーペアを生成します。

```bash
cd ./server

docker compose exec app bash -lc "mkdir -p /var/www/html/laravel/storage/keys && \
  openssl genrsa -out /var/www/html/laravel/storage/keys/jwtRS256.key 4096 && \
  openssl rsa -in /var/www/html/laravel/storage/keys/jwtRS256.key -pubout -out /var/www/html/laravel/storage/keys/jwtRS256.key.pub"
```

このコマンドは既存鍵を上書きします。`backend/laravel/storage/keys/`に鍵が存在しない初回セットアップ時だけ実行し、生成した秘密鍵と公開鍵をGit管理対象へ追加しないでください。

### 3. WordPressのインストール・セットアップ

Dockerコンテナーが起動後、WordPressのインストール・セットアップを行います。

```bash
cd ./server

# WordPressのインストール・セットアップ
make wp-setup
```

`make wp-setup`には公開用シンボリックリンクの作成も含まれるため、続けて`make wp-symlinks`を実行する必要はありません。

`make wp-setup`はWordPress本体、設定、Database、公開Linkを作成する初回構築用コマンドです。既存のWordPress、`wp-config.php`、Database、Plugin、Theme、Uploadがある環境へ再実行しないでください。[Issue #39](https://github.com/DesignSupply/startify-app/issues/39)が解決するまでは、既存環境で`make wp-symlinks`を個別に再実行せず、公開Linkの状態を[Dockerローカル開発環境 セットアップ・運用手順](specifications/server/docker/setup-and-operations.md)にしたがって確認してください。

https://cms.localhost/ にアクセスすることでWordPressのサイトトップページが表示されます。

### 4. Next.jsのインストール（ローカル環境）

Node.jsのバージョンは `frontend/next/.nvmrc` で `22.12.0` に固定しています（`engines.node` は `>=22.12.0 <23`）。npmは `10.8.2` を使用します。

```bash
cd ./frontend/next
nvm use

npm ci
npm run check
npm run build
```

初回セットアップ時や、Next.jsなどの依存パッケージと `package-lock.json` が更新された直後は、古い依存関係が残らないように `npm ci` を実行してください。`npm ci` は既存の `node_modules` を削除し、`package-lock.json` に記録されたバージョンをクリーンインストールします。依存関係に変更がない通常の開発では毎回実行する必要はなく、`npm run dev` から開始できます。

#### Cloudflare Workers Static Assets 対応

Next.jsは **Static Export** 構成で、生成物 `out/` を **Cloudflare Workers Static Assets** へ配信します（OpenNext／SSRは対象外）。Development／Staging／Productionの環境区分と詳細な設定・運用手順は、仕様書 [`specifications/infrastructure/cloudflare/next-static-deployment.md`](specifications/infrastructure/cloudflare/next-static-deployment.md) を参照してください。

**通常開発**

```bash
cd ./frontend/next
npm run dev
```

http://localhost:3000/ にアクセスすることでNext.jsのアプリケーショントップページが表示されます。

**品質確認**

```bash
cd ./frontend/next
npm run check
```

`npm run check` はlint・型チェック・テストの一括実行です。個別実行する場合は `npm run typecheck` や `npm run test` も利用できます。

**Cloudflare互換性確認**

```bash
cd ./frontend/next
npm run build:cf
npm run preview:cf
```

- 日常開発は `npm run dev` を使用する
- Cloudflare互換性確認はWrangler Previewを使用する
- `out/` は生成物のためGit管理対象外

### 5. Vite静的ページ制作環境の構築

Viteは、HTML、Pug、Handlebars、React、Vue.jsなどを使用できる静的ページ制作向けのボイラープレートです。ローカル環境にNode.jsをインストール後、次のコマンドで起動します。

```bash
cd ./frontend/vite

# インストール
npm ci

# ローカルサーバー起動
npm run dev
```

http://localhost:2000/ でローカルサーバーが起動します。

### 6. Astro静的ページ制作環境の構築

Astroは、Astroコンポーネント、Markdown、React、Vue.jsなどを使用できる静的ページ制作向けのボイラープレートです。ローカル環境にNode.jsをインストール後、次のコマンドで起動します。

```bash
cd ./frontend/astro

# インストール
npm ci

# ローカルサーバー起動
npm run dev
```

http://localhost:2000/ でローカルサーバーが起動します。

### 7. StorybookのUIコンポーネント管理

ローカル環境にNode.jsをインストール後、StorybookのUIコンポーネント管理環境を構築します。依存関係は`npm ci`でlockfileから導入します。

```bash
cd ./frontend/ui

# 依存関係の導入
npm ci

# Playwright Chromiumの導入（初回Setup時、またはPlaywright更新後）
npm run playwright:install

# ローカルサーバー起動
npm run storybook

# Storybook Test（Render・Accessibility検査）
npm run test:storybook

# TypeScript型チェック
npm run typecheck

# 静的Build
npm run build-storybook
```

Playwright PackageとChromium実行ファイルは別に導入されます。`npm run playwright:install`は初回Setup時またはPlaywright更新後に実行します。通常の開発時に毎回Chromiumを再インストールする必要はありません。

StorybookはPort `6006`で起動します。Test、型チェック、静的Buildは個別に実行できます。

http://localhost:6006/ でローカルサーバーが起動します。

---

## 動作確認環境

- Docker Engine: `27.4.0`
- Docker Compose: `2.31.0`
- Docker Desktop: `4.37.2`
- Next.js Node.js: `22.12.0`（`frontend/next/.nvmrc`の指定値）
- Next.js npm: `10.8.2`（`frontend/next/package.json`の指定値）

---

## AI駆動開発

本環境では、特定のEditorやAgent製品に依存しないAI駆動開発用ドキュメントを収録しています。Agent共通の必須指示は `AGENTS.md`、現在の設計と実装規約は `specifications/` を参照し、人間とAIが同じContextを共有して開発を進めます。

Startify-Appは、自社サイトや各種プロジェクトの派生基盤としても利用できます。派生時は、採用する構成を [`specifications/project/profile.md`](specifications/project/profile.md) で定義してください。詳細な構成選択・初期化手順も同文書を参照してください。

---

## 備考

- Dockerローカル開発環境の構成は [概要仕様](specifications/server/docker/overview.md)、構築と操作は [セットアップ・運用手順](specifications/server/docker/setup-and-operations.md) を参照してください。
- Dockerローカル開発環境の変数は `server/.env` で管理します。Laravelや各フロントエンドの環境変数は、それぞれのアプリケーション配下で管理します。
- この開発環境では、Laravel、WordPressのアプリケーションディレクトリがドキュメントルート外にインストールされる形になります。
