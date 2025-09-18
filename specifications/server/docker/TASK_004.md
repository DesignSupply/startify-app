---
title: Docker開発環境構築手順:SSL証明書導入
id: docker_task_004
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Docker開発環境構築手順：SSL証明書導入

---

## 1. 証明書インストール（ホストOS）

ローカル開発環境で `localhost` / `cms.localhost` / `testing.localhost`（API用に `api.localhost` も追加）をHTTPSで利用できるよう、ホストOS側で開発用CAの信頼と証明書発行を行います。
作成した証明書はnginxコンテナーにマウントします。

**前提**
- ホストOS: macOS（Homebrew利用）
- ブラウザで“警告なし”のHTTPSを利用するため、ローカルCAの信頼作業が必要です（初回のみ）

### 1.1. ツールのインストール（ホストOS）

```bash
# mkcert のインストール（未導入の場合）
brew install mkcert

# システムのトラストストアにローカルCAを登録（初回のみ）
mkcert -install
```

### 1.2. 証明書の発行（SANに複数ホストを含める）

```bash
cd ./server/docker/nginx
mkdir -p certs
cd certs

# 利用する全ホスト名を指定
mkcert localhost cms.localhost testing.localhost api.localhost

# 例）以下のようなファイルが生成されます（ファイル名の +N はホスト数で変動）
#   localhost+3.pem        # サーバー証明書
#   localhost+3-key.pem    # サーバー秘密鍵

# 生成物を nginx/certs 配下に配置（既に certs 直下に生成されていれば不要）
# mv localhost+3.pem certs/
# mv localhost+3-key.pem certs/
```

- 補足: ホスト名が解決しない場合は `/etc/hosts` に以下を追記してください（多くの環境では不要）。

```text
127.0.0.1   localhost cms.localhost testing.localhost api.localhost
```

下記の成果物が `/server/docker/nginx/` 配下に含まれているかを確認。

- `/certs/localhost+N.pem`
- `/certs/localhost+N-key.pem`

**注意**
作成した証明書と秘密鍵はGitの追跡対象外とすること。

### 1.3. 検証（証明書ファイルの存在確認）

```bash
ls -l /server/docker/nginx/certs | cat
```

---

## 2. SSL設定

作成した証明書をnginxコンテナーにマウントし、`localhost` / `cms.localhost` / `testing.localhost` / `api.localhost` の443番ポートを有効化します。
80番はHTTPSへリダイレクトします。

### 2.1. docker-compose.yml の更新（443公開・証明書マウント）

- 対象ファイル: `/server/docker-compose.yml`
- サービス: `web`
- 変更内容:
  - `ports` に "443:443" を追加
  - `volumes` に `./docker/nginx/certs:/etc/nginx/certs:ro` を追加

### 2.2. nginx.conf の更新（443サーバーブロック追加・80はHTTPSへリダイレクト）

- 対象ファイル: `/server/docker/nginx/nginx.conf`
- 対応方針:
  - httpブロック直下に最低限のSSL設定を追加
  - 各ホストについて、`listen 80` のサーバーブロックはリダイレクト専用にし、`listen 443 ssl` のサーバーブロックを新設
  - 証明書パスは `/etc/nginx/certs/localhost+N.pem` と `/etc/nginx/certs/localhost+N-key.pem`（実際のファイル名に合わせて置換）

**注意**
- 開発環境ではHSTS（Strict-Transport-Security）は有効化しないことを推奨します。
- すべてのホストで同一のSAN証明書を使い回して問題ありません（開発用途）。

---

## 3. コンテナー再起動

```bash
cd ./server
make build
make up
```

---

## 4. 動作確認

- ブラウザで以下にアクセスし、警告なしで表示されること
  - `https://localhost`
  - `https://cms.localhost`
  - `https://testing.localhost`
- 追加の技術的確認（任意）

```bash
# 証明書・TLSの疎通確認
echo | openssl s_client -connect localhost:443 -servername localhost 2>/dev/null | openssl x509 -noout -subject -issuer

# ヘッダー確認
curl -I https://localhost | cat
```

問題なく表示されたら、環境変数ファイルに記述されている各URLのプロトコルもhttpsに変更しておく。

### 4.1 トラブルシュート

- ブラウザが“この接続ではプライバシーが保護されません”と出る:
  - `mkcert -install` が未実行、または別ユーザー/キーチェーンに導入されている可能性
  - 生成ファイル名（`localhost+N.pem`）とnginx設定のパス不一致
- `api.localhost` も将来利用する場合:
  - 8.3の発行コマンドに含め直して再発行し、nginxの `server_name` と証明書を更新

---

## 5. API用のサーバーブロック追加

`api.localhost` をAPI専用ホストとしてHTTPS対応します。docker-composeの変更は不要です（443公開と証明書マウントは済）。

### 5.1. nginx.conf の追記

- 対象ファイル: `/server/docker/nginx/nginx.conf`
- 追記内容:
  - `api.localhost` 向け80→443リダイレクト
  - `listen 443 ssl` のHTTPSサーバーブロック
  - 証明書パスは実際のファイル名（例: `localhost+3.pem`）に合わせて置換

### 5.2. コンテナー再起動（反映）

```bash
cd ./server
docker compose restart web | cat
```

### 5.3. 動作確認

```bash
# HTTPS応答
curl -I https://api.localhost | sed -n '1,5p'

# HTTP→HTTPS リダイレクト
curl -I http://api.localhost | sed -n '1,5p'
```

期待される結果:
- `https://api.localhost` が200などの応答（Laravelルート未定義時は404も許容）
- `http://api.localhost` が301で `https://api.localhost` にリダイレクト

### 5.4. 補足
- LaravelでAPI専用のベースURLを使う場合は、必要に応じて `.env` の `APP_URL` を `https://api.localhost` に更新してください。

---
