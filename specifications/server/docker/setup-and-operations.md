---
title: Dockerローカル開発環境 セットアップ・運用手順
status: current
last_updated: 2026-08-21
related_paths:
  - server/.env.example
  - server/docker-compose.yml
  - server/Makefile
  - server/docker/nginx/certs/
  - backend/laravel/.env.example
  - backend/_webroot/
  - backend/wordpress/
  - backend/_cms-webroot/
  - backend/_testing-webroot/
---

# Dockerローカル開発環境 セットアップ・運用手順

Startify-AppのDockerローカル開発環境について、初回セットアップ、日常操作、Application Setup、検証、トラブルシューティング、破棄時の注意を定義します。

Service構成、Network、Volume、Host、環境変数の責務、本番環境との境界は `specifications/server/docker/overview.md` を参照してください。

## 1. 前提

本書の手順は、現在のRepositoryを取得済みで、Project Rootを起点に操作することを前提とします。

Host OSには次が必要です。

- Docker DesktopまたはDocker Composeを利用できるDocker環境
- `make`
- ローカルHTTPS証明書を発行する `mkcert`
- macOSで `mkcert` をHomebrewから導入する場合はHomebrew

Docker環境は次のPortを使用します。競合するServiceがある場合は、先に停止するかCompose設定を調整します。

- `80`: Nginx HTTP
- `443`: Nginx HTTPS
- `3306`: MariaDB
- `1025`: Mailpit SMTP
- `8025`: Mailpit Web UI

Composeの公開PortはHostのLoopback Addressに限定されていません。信頼できないNetworkへ接続している環境では、Host Firewallなども含めて公開範囲を確認してください。

## 2. 初回セットアップの全体順序

初回セットアップは、原則として次の順序で行います。

1. `server/.env` を準備する
2. ローカルHTTPS証明書を発行する
3. Docker ImageをBuildする
4. Containerを起動する
5. Laravelをセットアップする
6. 必要な場合はJWT鍵を生成する
7. WordPressをセットアップする
8. Host、Database、Mailpit、Testを検証する

既存環境へ再実行する場合は、`.env`、APP_KEY、Database、Upload、秘密鍵、Symbolic Linkを上書きまたは削除しないよう、各手順の注意を確認してください。

## 3. Docker環境変数の準備

`server/.env` が存在しない場合だけ、Sampleから作成します。

```bash
cd server
cp .env.example .env
```

すでに `server/.env` が存在する場合はCopyせず、現在値を維持します。設定を初期状態から作り直す場合も、既存Fileの削除によるDatabase接続情報や管理者情報への影響を確認してから操作してください。

`server/.env` では、少なくとも次を環境ごとに確認します。

- Compose Project名とApplication名
- Laravel用Database名、User名、Password
- WordPress用Database名、User名、Password
- WordPress管理者User名、Password、Email Address
- WordPressのHome URLとSite URL

Sample値はローカル開発用です。本番の認証情報を設定せず、Git管理対象へ追加しません。

MariaDBの初期化変数は、Database Volumeが空の初回起動時に使用されます。既存の `db-store` がある状態で `server/.env` のDatabase名、User名、Passwordを変更しても、既存DatabaseやUserには自動反映されません。

## 4. ローカルHTTPS証明書

### 4.1 `mkcert` の準備

macOSで未導入の場合は、Host OS上でインストールします。

```bash
brew install mkcert
mkcert -install
```

FirefoxでローカルCAを認識できない場合は、必要に応じてNSSを導入してから `mkcert -install` を再実行します。

```bash
brew install nss
mkcert -install
```

### 4.2 証明書の発行

Nginx設定が参照する4Hostを含む証明書を、次のDirectoryで発行します。

```bash
cd server/docker/nginx/certs
mkcert localhost cms.localhost testing.localhost api.localhost
```

現在のNginx設定は次のFile名を参照します。

```text
server/docker/nginx/certs/localhost+3.pem
server/docker/nginx/certs/localhost+3-key.pem
```

Host数や生成順序によってFile名が異なる場合は、生成物と `server/docker/nginx/nginx.conf` の参照先を一致させます。証明書と秘密鍵はGit管理対象外です。

通常は `*.localhost` を名前解決できます。環境によって解決できない場合に限り、Host OSのhosts設定を確認します。

```text
127.0.0.1 localhost cms.localhost testing.localhost api.localhost
```

## 5. Buildと起動

ImageをBuildします。

```bash
cd server
make build
```

ContainerをBackgroundで起動します。

```bash
cd server
make up
```

起動状態を確認します。

```bash
cd server
make ps
```

現在はNginx、PHP-FPM、MariaDBに明示的なHealth Checkがありません。`Up`はApplicationから利用可能な状態まで保証しないため、起動直後にDatabase接続へ失敗した場合はMariaDBの起動完了を待ってから再実行します。

## 6. Laravelの初回セットアップ

### 6.1 Composer依存のインストール

```bash
cd server
make laravel-install
```

### 6.2 Laravel `.env` とAPP_KEY

現在の `make laravel-keygen` は、`backend/laravel/.env.example` を `.env` へCopyしてからAPP_KEYを生成します。

```bash
cd server
make laravel-keygen
```

このCommandを再実行すると、既存の `backend/laravel/.env` とAPP_KEYを上書きします。既存 `.env` を保護する改善はIssue #38で管理しているため、修正されるまでは初回作成時に限って実行します。

### 6.3 公開Storage Link

Laravel標準のStorage Linkを作成した後、現在のDocument RootへLinkを切り替えます。

```bash
cd server
make laravel-storage-link
```

```bash
cd server
make laravel-storage-link-change
```

最終的な公開Linkは、Container内で次を参照します。

```text
/var/www/html/_webroot/storage
  → /var/www/html/laravel/storage/app/public
```

現在の `make laravel-storage-link-change` は、公開先に正しいLinkが存在するか確認せずに `ln -s` を実行します。正しいLinkがすでに存在する状態で再実行すると、Link先の中へ意図しない自己参照Linkを作成する可能性があります。この2段階処理をLaravelの設定と1つのMake Commandへ整理し、安全に再実行できるようにする改善はIssue #40で管理します。

Issue #40が解決するまでは、上記2CommandをLinkが存在しない初回セットアップ時に限って実行し、既存環境のLink修復を目的として再実行しません。

### 6.4 MigrationとSeeder

Migrationを実行します。

```bash
cd server
make laravel-migrate
```

ローカル確認用Dataが必要な場合はSeederを実行します。

```bash
cd server
make laravel-seed
```

SeederはDatabase Dataを変更します。既存Dataがある環境では、Seederの内容と重複時の挙動を確認してから実行してください。

### 6.5 JWT鍵

JWT認証を使用する場合は、Git管理対象外の `backend/laravel/storage/keys/` へ秘密鍵と公開鍵を生成します。

```bash
cd server
docker compose exec app bash -lc "mkdir -p /var/www/html/laravel/storage/keys && openssl genrsa -out /var/www/html/laravel/storage/keys/jwtRS256.key 4096 && openssl rsa -in /var/www/html/laravel/storage/keys/jwtRS256.key -pubout -out /var/www/html/laravel/storage/keys/jwtRS256.key.pub"
```

既存鍵を上書きすると発行済みTokenへ影響するため、鍵が存在しない初回セットアップ時に限って実行します。秘密鍵と公開鍵をGit管理対象へ追加しません。

## 7. WordPressの初回セットアップ

WordPress本体はDocument Root外の `backend/wordpress/` へ配置します。NginxのDocument Rootは `backend/_cms-webroot/` です。

現在の一括Setup Commandは、WordPress本体のDownload、設定、Database作成、Install、Update、公開Link作成を順番に実行します。ただし、最後に呼び出す `wp-symlinks` は現在採用しているディレクトリ単位のLinkを再現しません。この不一致はIssue #39で管理しているため、Issue解決前は新規環境でもCommand実行後に公開Linkの状態を確認する必要があります。

```bash
cd server
make wp-setup
```

このCommandは初回構築用です。既存のWordPress、`wp-config.php`、Database、Plugin、Theme、Uploadがある環境へ無条件に再実行しません。

現在の `wp-symlinks` は、WordPress直下の各項目を `backend/_cms-webroot/wordpress/` 配下へ個別にLinkする実装です。

現在の正しい公開Linkは次です。

```text
backend/_cms-webroot/wordpress
  → ../wordpress
```

Issue #39が解決するまでは、`make wp-symlinks`を既存環境へ再実行せず、現在のLinkを変更・削除しません。

WordPressのURLは次の役割に分かれます。

```text
WP_HOME=https://cms.localhost
WP_SITEURL=https://cms.localhost/wordpress
```

### 7.1 WordPressのGit管理

`backend/wordpress/.gitignore` に基づき、Downloadまたは実行時に生成される次の対象はGit管理へ追加しません。

- WordPress Core
- `wp-config.php`
- `wp-content/uploads/`
- Cache、Log、Backup、Upgrade時の生成物
- 第三者ThemeとPlugin

独自実装は例外として管理します。現在は `backend/wordpress/wp-content/themes/startify-classic-theme/` を追跡し、`backend/wordpress/wp-content/plugins/custom-plugin/` を追跡可能な例外Pathとして定義しています。

WordPressのDownload、Setup、Update、Plugin操作後は、Core、設定、Upload、第三者Package、生成物が意図せずGitの変更対象になっていないことを確認します。

## 8. 日常のDocker操作

各Commandは `server/` で実行します。

### 8.1 起動・停止・再起動

```bash
cd server
make up
```

```bash
cd server
make down
```

```bash
cd server
make restart
```

通常の `make down` ではMariaDBのNamed Volumeを維持します。

### 8.2 状態とLog

```bash
cd server
make ps
```

```bash
cd server
make logs
```

`make logs` は継続してLogを表示します。終了する場合は `Ctrl+C` を使用します。

### 8.3 Container Shell

```bash
cd server
make server-web
```

```bash
cd server
make server-app
```

```bash
cd server
make server-db
```

```bash
cd server
make server-smtp
```

## 9. Application操作

### 9.1 Laravel

Cacheと設定を削除します。

```bash
cd server
make laravel-cache-clear
make laravel-route-clear
make laravel-config-clear
```

Laravelの最適化Cacheを生成します。

```bash
cd server
make laravel-optimize
```

Route一覧を確認します。

```bash
cd server
make laravel-route
```

Testを実行します。

```bash
cd server
make laravel-test
```

### 9.2 WordPress

WordPress CoreとDatabaseをUpdateします。

```bash
cd server
make wp-update-all
```

Pluginを指定して操作する場合は `PLUGIN` を渡します。

```bash
cd server
make wp-plugin-install PLUGIN=plugin-slug
```

```bash
cd server
make wp-plugin-update PLUGIN=plugin-slug
```

```bash
cd server
make wp-plugin-delete PLUGIN=plugin-slug
```

CoreやPluginのUpdate前には、互換性、変更内容、復旧方法を確認します。本番環境の更新手順としては使用しません。

## 10. 動作確認

### 10.1 Host

| URL | 期待する結果 |
| --- | --- |
| `https://localhost` | Laravel MPAを表示できる |
| `https://api.localhost` | Laravel APIへ接続できる |
| `https://cms.localhost` | WordPress Siteを表示できる |
| `https://cms.localhost/wordpress/wp-admin/` | WordPress管理画面へ接続できる |
| `https://testing.localhost/testing-app.php` | PHP設定を確認できる |
| `http://localhost:8025` | Mailpit Web UIを表示できる |

HTTPのApplication HostはHTTPSへ301 Redirectします。

WordPressの初回セットアップ後は、管理画面へログインし、現在構成で次の基本操作ができることを確認します。

- 投稿を公開、編集、削除できる
- Themeと管理画面のAssetを読み込める
- 必要に応じて、検証用Pluginをインストール、更新、削除できる

投稿やPluginを使用した確認はローカル環境で行い、不要になった検証DataやPackageを残しません。既存Dataや開発中の独自Theme・Pluginを検証目的で変更または削除しません。

### 10.2 診断Page

`testing.localhost` はローカル専用です。

- `/testing-app.php` は `phpinfo()` を表示し、環境変数を含む情報を公開する可能性がある
- `/testing-smtp.php` はGET AccessによりMailpitへテストメールを送信する
- `/preview/` はStatic Preview用で、現在Root Indexは存在しない

`server/.env`には開発専用またはDummyの値だけを使用し、診断Pageを外部へ公開しません。Mailpitの受信メールはContainer再作成時に失われます。

### 10.3 構成変更時の検証

変更範囲に応じて、次を確認します。

```bash
cd server
docker compose config --quiet
```

```bash
cd server
make ps
```

```bash
cd server
docker compose exec web nginx -t
```

Host OSから証明書のSubjectとIssuerを確認します。

```bash
echo | openssl s_client -connect localhost:443 -servername localhost 2>/dev/null | openssl x509 -noout -subject -issuer
```

HTTPSの応答とHTTPからHTTPSへのRedirectを確認します。

```bash
curl -I https://localhost
```

```bash
curl -I http://localhost
```

必要に応じて `cms.localhost`、`testing.localhost`、`api.localhost` も同じ方法で確認します。HTTPは同じHostのHTTPSへ301 Redirectし、HTTPSはApplicationまたは対象Routeに応じたStatusを返すことを確認します。

```bash
cd server
make laravel-test
```

```bash
cd server
make laravel-route
```

検証目的でDatabase Volume、既存 `.env`、JWT鍵、証明書、Uploadを削除しません。

## 11. トラブルシューティング

### 11.1 Containerが起動しない

次を確認します。

- Dockerが起動している
- `server/.env` が存在する
- Composeが使用する環境変数が定義されている
- Port `80`、`443`、`3306`、`1025`、`8025` が競合していない
- BuildまたはContainer LogにErrorがない

```bash
cd server
docker compose config
```

```bash
cd server
make ps
make logs
```

### 11.2 Databaseへ接続できない

次を確認します。

- `app` ContainerからのDatabase Hostが `db` になっている
- `server/.env` とApplication側のDatabase設定が一致している
- MariaDBの起動が完了している
- 既存VolumeのDatabase・User・Passwordと現在の `.env` が一致している

既存Volumeがある状態では、`.env` の変更だけでDatabaseやUserは再作成されません。Dataを保持する必要がある場合は、Volumeを削除せずDatabase側の状態を確認します。

### 11.3 HTTPSで警告または起動Errorになる

次を確認します。

- `mkcert -install` を実行したUserとBrowserを使用している
- 4Hostを含む証明書を発行している
- 証明書のFile名とNginx設定が一致している
- 証明書と秘密鍵が `server/docker/nginx/certs/` にある
- Nginx設定の構文が正しい

```bash
cd server
docker compose exec web nginx -t
```

### 11.4 設定変更がLaravelへ反映されない

LaravelのCacheを削除します。

```bash
cd server
make laravel-cache-clear
make laravel-route-clear
make laravel-config-clear
```

`.env`の作り直しを目的として `make laravel-keygen` を再実行しません。現在は既存 `.env` とAPP_KEYを上書きします。

### 11.5 WordPress公開Pathを読み込めない

次を確認します。

- `backend/wordpress/` にWordPress本体が存在する
- `backend/_cms-webroot/index.php` が `wordpress/wp-blog-header.php` を読み込んでいる
- `backend/_cms-webroot/wordpress` がWordPress本体を参照している
- `WP_HOME` と `WP_SITEURL` が現在のHTTPS URLと一致する

Issue #39が解決するまでは、誤ったPathを自動修復する目的で `make wp-symlinks`を実行しません。既存FileやDirectoryを削除せず、状態を確認してから対応します。

## 12. 再Buildと破棄

DockerfileやBuild Contextを変更した場合は、対象Imageを再Buildします。

```bash
cd server
make build
make up
```

`make destroy` は次を削除します。

- Compose Container
- Composeで使用するImage
- MariaDBのNamed Volume
- Orphan Container

Database Dataを含むVolumeを削除するため、通常の停止や再起動には使用しません。復元できるBackupがあり、環境を完全に作り直すことが明示的に必要な場合だけ実行します。

```bash
cd server
make destroy
```

Laravel・WordPressのSource、Application `.env`、Upload、証明書、JWT鍵はBind MountされたHost側に存在します。`make destroy`の対象外であっても、別途削除しないよう注意してください。

## 13. 既知の課題

| Issue・状態 | 操作への影響 |
| --- | --- |
| [#36](https://github.com/DesignSupply/startify-app/issues/36) | Build時に取得するImage・ToolのVersionが変化する可能性がある |
| [#37](https://github.com/DesignSupply/startify-app/issues/37) | MariaDB DockerfileとComposeの環境変数責務が重複している |
| [#38](https://github.com/DesignSupply/startify-app/issues/38) | `make laravel-keygen`の再実行で既存 `.env` とAPP_KEYを上書きする |
| [#39](https://github.com/DesignSupply/startify-app/issues/39) | `make wp-symlinks`が現在のディレクトリ単位のLinkを再現しない |
| [#40](https://github.com/DesignSupply/startify-app/issues/40) | `make laravel-storage-link`と公開Link作成を安全に再実行できない |

Issue解決時は、実装、`README.md`、`specifications/server/docker/overview.md`、本書を同じ現在状態へ更新します。
