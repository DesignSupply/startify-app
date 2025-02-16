# 環境構築手順

---

## 0. Cursor Composerの準備

### 0.1. .cursorrulesファイルの作成

下記の場所にファイルを作成します

- `/server/.cursorrules`

### 0.2. プロジェクト仕様書の作成

下記の場所にファイルを作成します

- `/specifications/PROJECT_OVERVIEW.md`
- `/specifications/ENV_PROCEDURES.md`

### 0.3. Composer使用準備

.cursorrulesファイルと各種プロジェクト仕様書を読み込ませて、Composerを使用する準備をします

---

## 1. Docker環境構築

### 1.1. Docker用環境変数ファイル作成

下記の場所にファイルを作成します

- `/server/.env`

### 1.2. 各種イメージ用Dcokerfileの作成

下記の場所にファイルを作成します

- `/server/docker/nginx/Dockerfile`
- `/server/docker/php/Dockerfile`
- `/server/docker/mysql/Dockerfile`
- `/server/docker/mailpit/Dockerfile`

### 1.3. MySQL用設定ファイル、nginx用設定ファイル、PHP用設定ファイルの作成

下記の場所にファイルを作成します、各設定ファイルは、Dockerのコンテナーにマウントされるようにします

- `/server/docker/mysql/my.cnf`
- `/server/docker/nginx/nginx.conf`
- `/server/docker/php/php.ini`

### 1.4. docker-compose.ymlの作成

下記の場所にファイルを作成します、イメージ、コンテナー、ボリューム名など、環境変数ファイルで指定した名前が設定されるようにします

- `/server/docker-compose.yml`

### 1.5. Makefileの作成

下記の場所にファイルを作成します、Docker操作、Laravel、WordPressを扱う中で必要となるコマンドを登録します

- `/server/Makefile`

### 1.6. Dockerイメージのビルド、コンテナーの起動

```
cd /server
make build
make up
```

### 1.7. 動作確認

- 環境変数で設定した情報でローカル環境のデータベースに接続できることを確認します
- `/backend/_webroot/testing-app.php` を作成、`http://localhost/testing-app.php` にアクセスして、PHPの情報が表示されることを確認します
- `/backend/_webroot/testing-smtp.php` を作成、`http://localhost/testing-smtp.php` にアクセスして、メール送信テスト用ファイルを使用して送信されることを確認します
- `http://localhost:8025/` にアクセスして、MailpitのWebメール画面が表示、ならびにテストメールの受信ができることを確認します

_（〜4h）_

---

## 2. Laravel環境構築

### 2.1. Laravelプロジェクトの作成

Dockerコンテナー内でLaravelプロジェクトを作成します、Laravelインストール後にマウントされた `/backend/laravel` ディレクトリ配下にソースコードが同期されることを確認します

```
cd /server
make laravel-install
```

### 2.2. 環境変数ファイルの設定、キーの生成

Dockerで使用している環境変数をLaravelの.envファイルに反映し、キーを生成します

```
cd /server
make laravel-keygen
```

### 2.3. データベースの作成、マイグレーション

```
cd /server
make laravel-migrate
```

ローカル環境のデータベースにアクセスし、Laravelのマイグレーションによってテーブルが作成されていることを確認します

### 2.4. ストレージのシンボリックリンク作成

```
cd /server
make laravel-storage-link
```

アプリケーションのプロジェクトルートがドキュメントルート外にある場合にはシンボリックリンクの場所を変更します

```
cd /server
make laravel-storage-link-change
```

シンボリックリンクの変更後、下記の場所にストレージのシンボリックリンクが作成されているかを確認します

- /backend/_webroot/storage

### 2.7. アプリケーションエントリーポイントファイルの作成

下記の場所にアプリケーションのエントリーポイントファイルを作成します、既存のLaravelのエントリーポイントファイルをコピーし、Laravelのプロジェクトルートを参照するように変更します

- `/backend/_webroot/index.php`

### 2.5. テスト・表示確認

```
cd /server
make laravel-test
```

- テストが実行され、テスト結果が表示されることを確認します
- `http://localhost/` にアクセスして、Laravelのテストページが表示されることを確認します

_（〜0.5h）_

---
