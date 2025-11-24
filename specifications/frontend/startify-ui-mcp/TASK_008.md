---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:ロギング／エラーハンドリング／設定管理（.env.local）
id: startify-ui-mcp_task_008
version: 1.0.0
last_updated: 2025-11-24
purpose: MCPサーバーおよびPlaygroundにおけるログ出力・例外処理・設定値の管理方法を定義し、今後の機能追加に耐える最小の基盤を整える
target_readers: ウェブエンジニア（バックエンド、フロントエンド）
---

## Startify-UI MCPサーバーワークスペース構築タスクリスト: ロギング／エラーハンドリング／設定管理（.env.local）

---

## 1. 目的と完了条件

### 1.1. 目的

- 開発・検証時に有用なログを一定の粒度で取得できるようにし、将来の運用を意識したログレベル運用を定義する。
- 例外発生時の失敗レスポンスやユーザー向けメッセージの方針を定め、MCPツール実行時の失敗を再現性高く追跡できるようにする。
- `.env.local` などの環境変数で設定値を切り替え可能にし、ファイルパスやログレベル等の変更をコード変更なしで行えるようにする。

### 1.2. 完了条件

- ドキュメント（本ファイル）で方針・実装手順・検証項目が定義されている。
- `.env.local.example` の雛形が定義され、設定キーと意味が明文化されている（本タスクではファイル作成手順のみ、実装は次タスクで可）。
- ログレベルの運用（debug/info/warn/error）と出力形式（テキスト/将来JSON化などの拡張余地）が定義されている。
- 例外の取り扱い（入力検証エラー、内部エラー、外部依存エラー）の区分けと返却方針が定義されている。

---

## 2. 対象範囲

- MCPサーバー（Node.js + TypeScript, ESM/NodeNext）
- Playground（Vite + プレーンJS、最小UI）

---

## 3. 設計方針

### 3.1. ロギング

- 最小構成では `console` をラップしたロガーを採用（将来 `pino` 等の導入余地を残す）。
- レベル: `debug` / `info` / `warn` / `error` を提供。
- 既定レベルは `info`。`STARTIFY_LOG_LEVEL` で上書き可能（例: `debug`）。
- 出力形式はテキスト。将来の拡張としてJSON構造化出力を検討。
- ログメッセージにはプレフィックス `[MCP]`（サーバー）/`[PG]`（Playground）を付与。

### 3.2. エラーハンドリング

- 入力検証エラー（クライアント起因）: 400系相当の扱い。メッセージはユーザー向けに簡潔に、詳細はログに記録。
- 内部エラー（サーバー起因）: 500系相当。ユーザーには一般的な失敗メッセージを返し、詳細はログのみ。
- 外部依存エラー（ファイル/ネットワーク等）: 種別をログに明記（どの依存が失敗したか）。
- MCP `tools/call` ハンドラー内部は `try/catch` で囲み、失敗時は共通のエラー整形関数で戻す。
- PlaygroundはUI通知（`alert` など最小限）とコンソールログのみに留める（ビジネスロジックはサーバー側）。

### 3.3. 設定管理（.env.local）

- 設定値は `process.env` から取得する設計。開発では `.env.local` を読み込む。
- 代表的なキー（想定）:
  - `STARTIFY_LOG_LEVEL`: `debug` | `info` | `warn` | `error`（既定: `info`）
  - `STARTIFY_COMPONENTS_FILE`: コンポーネント定義の相対/絶対パス（既定: `mcp-server/config/components.yaml`）
  - `STARTIFY_TOKENS_DIR`: デザイントークンのディレクトリパス（既定: `frontend/startify-ui-mcp/design-tokens` 自動解決に委譲）
- 値が未設定の場合は既定値で動作。クリティカルなキーが欠落する場合は起動時に警告/エラーを記録。

---

## 4. 実装手順（提案、次タスクで反映）

> 本タスクは設計と手順の定義のみ。コード変更は次タスクで実装します。

### 4.1. `.env.local.example` の作成（ワークスペース直下）

```bash
cat > frontend/startify-ui-mcp/.env.local.example << 'EOL'
# ログレベル: debug | info | warn | error
STARTIFY_LOG_LEVEL=info

# コンポーネント定義ファイルのパス（相対または絶対）
# 例: frontend/startify-ui-mcp/mcp-server/config/components.yaml
# STARTIFY_COMPONENTS_FILE=

# デザイントークンのディレクトリ
# 例: frontend/startify-ui-mcp/design-tokens
# STARTIFY_TOKENS_DIR=
EOL
```

> その後、必要に応じて `.env.local` を作成し、`.gitignore` 下で管理する。

### 4.2. MCPサーバー: ロガーの導入（ラッパー）

- 追加ファイル案: `frontend/startify-ui-mcp/mcp-server/src/lib/logger.ts`
- 内容（概要）:
  - `getLogLevelFromEnv()` で `STARTIFY_LOG_LEVEL` を解決（未知値は既定にフォールバック）。
  - `createLogger(prefix: string)` で `debug/info/warn/error` を持つオブジェクトを返す。
  - 実装は `console` に委譲し、レベルフィルターとプレフィックス付与のみ行う。
- 利用箇所:
  - `src/server.ts` の起動ログ、`tools/call` ハンドラー内の開始/成功/失敗ログ。

### 4.3. MCPサーバー: 例外の整形

- 追加関数案: `formatToolError(e: unknown)` → `{ ok: false, error: { type, message, details? } }` を返す。
- 入力検証エラーは `type: 'validation'`、内部エラーは `type: 'internal'`、外部依存は `type: 'dependency'` とする。
- `tools/call` 側で `try/catch`、失敗時にロガーで `error` を記録し、`formatToolError()` を返却。

### 4.4. Playground: 最小ロガーとUI通知

- `src/ui.js` に `logInfo/logWarn/logError` を追加（`console.*` へのシンプルな委譲）。
- 失敗時は `alert('...')` を用いた最小通知（すでに導入済みのハンドラーを流用）。
- 将来はトーストUIやログパネルへの出力に拡張可能。

### 4.5. サーバー起動スクリプトでの `.env.local` 読み込み

- `frontend/startify-ui-mcp/mcp-server/package.json` の `scripts` に、ワークスペース直下の `.env.local` を読み込む指定を追加する。
- 相対パスは `../.env.local`（`mcp-server` から見た親ディレクトリ）を用いる。

```json
{
  "scripts": {
    "dev": "node --env-file=../.env.local --loader ts-node/esm src/server.ts",
    "start": "node --env-file=../.env.local dist/server.js"
  }
}
```

> 注: 本番運用では環境変数で直接上書きするか、環境固有の `.env` を指定する。

---

## 5. 動作確認（次タスクでの想定）

### 5.1. ログレベル切り替え

```bash
# .env.local に STARTIFY_LOG_LEVEL=debug を設定
cd frontend/startify-ui-mcp/mcp-server
npm run dev
```

期待結果:

- 起動時ログに `[MCP]` のデバッグ情報が含まれる。
- ツール実行時（将来の配線後）、成功/失敗の各ログがレベル別に出力される。

### 5.2. 想定失敗の確認（設計段階）

- `components.yaml` のパス不正 → 依存エラーとしてログ出力、ユーザー向けは汎用メッセージ。
- 入力の必須欠落 → 検証エラーとしてログ出力、ユーザー向けは簡潔な説明。

---

## 6. 検証項目と期待結果

- 検証項目: ログレベル制御
  - 期待: `.env.local` の `STARTIFY_LOG_LEVEL` に応じて出力が変わる
  - 実際:［ここに結果を記載］
  - 対応策: レベル比較ロジックの見直し、環境変数の読込確認

- 検証項目: 例外整形結果
  - 期待: 失敗レスポンスが `{ ok: false, error: { type, message } }` で統一
  - 実際:［ここに結果を記載］
  - 対応策: エラー分類の条件追加、詳細メッセージのサニタイズ

- 検証項目: 設定解決
  - 期待: `STARTIFY_COMPONENTS_FILE` / `STARTIFY_TOKENS_DIR` が未設定でも既定パスで解決
  - 実際:［ここに結果を記載］
  - 対応策: 既定パスの明示化、存在チェックと警告ログ

---

## 7. 注意点・今後の拡張

- 将来的に構造化ログ（JSON）とログ出力先（ファイル/外部集約）の切り替えを検討。
- 本番運用時は個人情報/機微情報をログに含めない方針を徹底。
- エラーメッセージはユーザー向け文言と内部用詳細を分離（前者はUI/言語、後者はログ）。
- `.env.local` のキー増加時は `.env.local.example` を必ず更新する。

---
