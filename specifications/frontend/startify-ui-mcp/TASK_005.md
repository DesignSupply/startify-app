---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装:get_tokens
id: startify-ui-mcp_task_005
version: 1.0.0
last_updated: 2025-11-18
purpose: /frontend/_design-tokens のYAMLを読み取り、MCPで参照可能なトークンJSONを返すget_tokensを設計・実装する
target_readers: ウェブエンジニア（バックエンド、フロントエンド）
---

# Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装:get_tokens

---

## 1. 読み込み対象の定義（`/frontend/startify-ui-mcp/design-tokens/*.yaml`）

### 1.1. 目的

プロジェクト共通のデザイントークン（YAML）をMCPサーバーから取得できるようにする。

### 1.2. 完了条件

- 読み込み対象: `/frontend/startify-ui-mcp/design-tokens/` 配下の `*.yaml`
  - 例: `color-scheme.yaml`, `size-scale.yaml`, `typography.yaml`, `grid-system.yaml`, `dropshadow.yaml`, `corner-style.yaml`, `easing.yaml`
- 返却形式: `Record<string, unknown>`（ファイル名をキー、パース結果を値とする）または用途別の整形オブジェクト
  - 読み込み対象ディレクトリが存在しない、または `*.yaml` が1件もない場合は例外を投げる

---

## 2. 依存パッケージの導入（YAMLパーサ）

### 2.1. 目的

YAMLを安全にパースするためのランタイム依存を追加する。

### 2.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server` に `yaml` が依存として追加されている

### 2.3. 作業

```bash
cd frontend/startify-ui-mcp/mcp-server
npm i yaml
```

---

## 3. ローダー実装（src/lib/tokens.ts）

### 3.1. 目的

YAMLファイル群を読み込み、単一のオブジェクトに統合して返すユーティリティを作る。

### 3.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/src/lib/tokens.ts` が存在する
- `loadDesignTokens()` が `/frontend/startify-ui-mcp/design-tokens` を探索し、`Record<string, unknown>` を返す
- 対象ディレクトリがない、または `*.yaml` が0件の場合は例外を投げる
- NodeNext + ESMに合わせ、相対インポートは拡張子付き（`.js`）表記で記述する

### 3.3. 作業（雛形）

```bash
cat > frontend/startify-ui-mcp/mcp-server/src/lib/tokens.ts << 'EOL'
import { readdirSync, readFileSync, existsSync } from 'node:fs';
import { resolve, basename, extname } from 'node:path';
import { parse } from 'yaml';

export type DesignTokens = Record<string, unknown>;

export function loadDesignTokens(cwd: string = process.cwd()): DesignTokens {
  const tokensDir = resolve(cwd, 'design-tokens');
  if (!existsSync(tokensDir)) {
    throw new Error(`design-tokens directory not found: ${tokensDir}`);
  }
  const files = readdirSync(tokensDir)
    .filter((f) => extname(f) === '.yaml')
    .sort();
  if (files.length === 0) {
    throw new Error(`no *.yaml found in: ${tokensDir}`);
  }
  const result: DesignTokens = {};
  for (const file of files) {
    const name = basename(file, '.yaml'); // 例: color-scheme
    const raw = readFileSync(resolve(tokensDir, file), 'utf-8');
    const data = parse(raw);
    result[name] = data;
  }
  return result;
}
EOL
```

---

## 4. MCP側の雛形配線（src/server.ts）

### 4.1. 目的

`get_tokens` ツールのスケルトンを追加（後続タスクでinitialize/tools/callと統合）。

### 4.2. 完了条件

- `src/server.ts` に `get_tokens` のコメント雛形が追記されている
- ESM対応の相対インポート拡張子（`.js`）に準拠している

### 4.3. 作業（雛形）

```bash
cat >> frontend/startify-ui-mcp/mcp-server/src/server.ts << 'EOL'
// --- get_tokens tool (skeleton) ---
// import { loadDesignTokens } from './lib/tokens.js';
// // server.on('tools/call', async ({ name }) => {
// //   if (name === 'get_tokens') {
// //     const tokens = loadDesignTokens(process.cwd());
// //     return { ok: true, data: tokens };
// //   }
// // });
EOL
```

---

## 5. 動作検証（暫定）

### 5.1. 目的

パーサとファイル解決の基本動作（dev/build）を確認する。

### 5.2. 完了条件

- `npm run dev` が起動し、`loadDesignTokens()` を一時呼び出しても例外が出ない（任意ログ）
- `npm run build && npm start` が成功

### 5.3. 作業（任意の一時確認）

```bash
# server.ts の main 内で一時ログ（検証後は削除）
# import { loadDesignTokens } from './lib/tokens.js';
# console.log('[MCP] tokens keys:', Object.keys(loadDesignTokens(process.cwd())));
```

---

## 6. 検証項目と期待結果（テンプレ）

- 検証項目: YAMLのパース
- 期待: 各 `*.yaml` が例外なくパースされ、キー（ファイル名ベース）が設定される
  - 実際:［ここに結果を記載］
  - 対応策: YAML記法の修正、パースエラーの詳細ログ出力

- 検証項目: パス解決
  - 期待: `/frontend/startify-ui-mcp/design-tokens` が参照される（起動ディレクトリに応じて `design-tokens` または `../design-tokens` を解決）
  - 実際:［ここに結果を記載］
  - 対応策: ベースディレクトリの明示指定（引数化）や `process.cwd()` の見直し

- 検証項目: ESMインポート
  - 期待: 相対パスは拡張子 `.js` 指定でエラーにならない
  - 実際:［ここに結果を記載］
  - 対応策: import文の拡張子修正、ビルド後の実ファイル構成確認

---
