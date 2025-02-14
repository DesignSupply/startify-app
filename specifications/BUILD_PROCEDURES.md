# プロジェクト環境構築手順

## 1. Docker環境構築

### 1.1. Docker用環境変数ファイル作成

下記の場所にファイルを作成します

- /server/.env

### 1.2. 各種イメージ用Dcokerfileの作成

下記の場所にファイルを作成します

- /server/docker/nginx/Dockerfile
- /server/docker/php/Dockerfile
- /server/docker/mysql/Dockerfile
- /server/docker/mailpit/Dockerfile

### 1.3. MySQL用設定ファイル、nginx用設定ファイル、PHP用設定ファイルの作成

下記の場所にファイルを作成します、各設定ファイルは、Dockerのコンテナにマウントされるようにします

- /server/docker/mysql/my.cnf
- /server/docker/nginx/nginx.conf
- /server/docker/php/php.ini

### 1.4. docker-compose.ymlの作成

下記の場所にファイルを作成します、イメージ、コンテナ、ボリューム名など、環境変数ファイルで指定した名前が設定されるようにします

- /server/docker-compose.yml

### 1.5. Makefileの作成

下記の場所にファイルを作成します、Docker操作、Laravel、WordPressを扱う中で必要となるコマンドを登録します

- /server/Makefile

### 1.6. Dockerイメージのビルド、コンテナの起動

```
cd /server
make build
make up
```

### 1.7. 動作確認

- 環境変数で設定した情報でローカル環境のデータベースに接続できることを確認
- /backend/publicディレクトリ配下にinfo.phpを作成、ブラウザで http://localhost にアクセスして、info.phpが表示されPHPの情報が表示されることを確認する
- /backend/publicディレクトリ配下にtesting-mail.phpを作成、ブラウザで http://localhost/testing-mail.php にアクセスして、メール送信テスト用ファイルが表示されることを確認する
- http://localhost:8025/ にアクセスして、MailpitのWebメール画面が表示、ならびにテストメールの受信ができることを確認する

**（ここまで4h）**

---

## 2. Laravelプロジェクトの作成
