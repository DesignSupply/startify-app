---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPサーバー雛形の作成
id: startify-ui-mcp_task_003
version: 1.0.0
last_updated: 2025-11-18
purpose: Node.js + TypeScript で MCP サーバーの最小構成を整え、以降のメソッド実装に備える
target_readers: ウェブエンジニア（バックエンド、フロントエンド）
---

# Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPサーバー雛形の作成

---

## 1. エントリーポイントの準備（src/server.ts）

### 1.1. 目的

MCPサーバーの起動エントリーを用意し、SDKの初期化土台を整える（メソッド実装は後続タスク）。

### 1.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/src/server.ts` に雛形コードが配置されている
- `npm run dev` で起動し、コンソールに起動ログが出力される
- `npm run build && npm start` が成功する

### 1.3. 作業

```bash
cd frontend/startify-ui-mcp/mcp-server
cat > src/server.ts << 'EOL'
import { fileURLToPath } from 'node:url';
import { dirname } from 'node:path';
// SDKは後続タスクで利用開始
// import { Server } from '@modelcontextprotocol/sdk/server';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

async function main(): Promise<void> {
  // 将来的にMCP Serverをここで初期化し、メソッドを登録する
  // const server = new Server({ name: 'startify-ui-mcp', version: '0.1.0' });
  // server.registerTool(...);
  // await server.start();

  console.log('[MCP] Startify-UI MCP server bootstrap ready.');
  console.log(`[MCP] entry: \${__dirname}`);
}

main().catch((err) => {
  console.error('[MCP] Fatal error:', err);
  process.exit(1);
});
EOL
```

---

## 2. npm スクリプトの確認

### 2.1. 目的

開発起動/ビルド/本番起動の基本コマンドが使用できる状態を保証する。

### 2.2. 完了条件

- `package.json` に以下のスクリプトが存在する  
  - `"dev": "node --loader ts-node/esm src/server.ts"`  
  - `"build": "tsc"`  
  - `"start": "node dist/server.js"`  

### 2.3. 作業（必要な場合のみ）

```bash
npx npm-add-script -k "dev" -v "node --loader ts-node/esm src/server.ts"
npx npm-add-script -k "build" -v "tsc"
npx npm-add-script -k "start" -v "node dist/server.js"
```

#### 2.4. ts-node 設定（ESM解決）

`package.json` に以下を追加して、開発時の拡張子解決（.js指定→.ts実体）を安定化させます。

```json
"ts-node": {
  "esm": true,
  "experimentalSpecifierResolution": "node"
}
```

---

## 3. 起動と検証

### 3.1. 目的

雛形としての起動ができ、後続タスクでメソッドを追加可能な状態であることを確認する。

### 3.2. 完了条件

- `npm run dev` 実行で、コンソールに `[MCP] Startify-UI MCP server bootstrap ready.` が表示される
- `npm run build && npm start` が成功し、同様のログが表示される

### 3.3. 作業

```bash
cd frontend/startify-ui-mcp/mcp-server
npm run dev
# 期待ログ:
# [MCP] Startify-UI MCP server bootstrap ready.
# [MCP] entry: /absolute/path/to/frontend/startify-ui-mcp/mcp-server/src

npm run build && npm start
# 同様のログが出力されること
```

> メモ: NodeNext + ESM では相対インポートに拡張子が必要です（例: `import ... from './lib/components.js'`）。開発時は ts-node ESM ローダーで `.js` 指定を `.ts` 実体に解決します。

---

## 4. 検証項目と期待結果（テンプレ）

- 検証項目: TypeScript実行（dev）
  - 期待: `npm run dev` が成功し、起動ログが表示される
  - 実際:［ここに結果を記載］
  - 対応策: ts-nodeの導入状態、tsconfig（NodeNext設定）を確認

- 検証項目: ビルド/本番実行
  - 期待: `npm run build && npm start` が成功し、起動ログが表示される
  - 実際:［ここに結果を記載］
  - 対応策: `outDir/rootDir` の設定、ビルド成果物の存在を確認

---
