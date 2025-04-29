---
title: Docker開発環境構築手順:Docker環境構築
id: docker_task_001
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Docker開発環境構築手順：Docker環境構築

---

## 1. Docker用環境変数ファイル作成

下記の場所にファイルを作成します、`COMPOSE_PROJECT_NAME` とアプリケーション名、データベース接続情報と、CMSのログイン情報を環境変数として設定します。

- `/server/.env`

```env
# Docker用環境変数ファイルのテンプレート例（Laravel）
COMPOSE_PROJECT_NAME=startify-app

APP_NAME=startify-app

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=master
DB_USERNAME=admin
DB_PASSWORD=secret
```

---

## 2. 各種イメージ用Dcokerfileの作成

下記の場所にファイルを作成します、それぞれ適切なバージョンのDockerイメージを使用します。

- `/server/docker/nginx/Dockerfile`
- `/server/docker/php/Dockerfile`
- `/server/docker/mysql/Dockerfile`
- `/server/docker/mailpit/Dockerfile`

---

## 3. MySQL用設定ファイル、nginx用設定ファイル、PHP用設定ファイルの作成

下記の場所にファイルを作成します、各設定ファイルは、Dockerのコンテナーにマウントされるようにします。

- `/server/docker/mysql/my.cnf`
- `/server/docker/nginx/nginx.conf`
- `/server/docker/php/php.ini`

---

## 4. docker-compose.ymlの作成

下記の場所にファイルを作成します、イメージ、コンテナー、ボリューム名など、環境変数ファイルで指定した値が紐づくようにします。

- `/server/docker-compose.yml`

---

## 5. Makefileの作成

下記の場所にファイルを作成します、Docker操作、Laravel、WordPressを扱う中で必要とされる `makeコマンド` を登録します。

- `/server/Makefile`

---

## 6. Dockerイメージのビルド、コンテナーの起動

下記のコマンドでDockerイメージのビルド、コンテナーの起動を行います。

```bash
cd /server
make build
make up
```

---

## 7. 動作確認

Dockerコンテナー起動後、以下の方法で動作確認を行います。

- ローカル環境データベース接続
  - 期待される結果: 
    - 環境変数で設定した情報でローカル環境のデータベースに接続できる。
- PHP詳細情報の表示
  - 必要なタスク: 
    - `php_info()` 関数を実装した、 `/backend/_testing-webroot/testing-app.php` ファイルを作成する。
  - 期待される結果: 
    - `http://testing.localhost/testing-app.php` にアクセスして、PHPの情報が表示される。
- ローカル環境でのメール送受信
  - 必要なタスク: 
    - `mail()` 関数を使用しメール送信のテスト処理を実装した、`/backend/_testing-webroot/testing-smtp.php` ファイルを作成する。
  - 期待される結果: 
    - `http://testing.localhost/testing-smtp.php` にアクセスして、メール送信テスト用ファイルを使用して送信されている。
    - `http://localhost:8025/` にアクセスして、MailpitのWebメール画面が表示され、送信されたテストメールの受信ができている。

---
