---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装:list_components
id: startify-ui-mcp_task_004
version: 1.0.0
last_updated: 2025-11-18
purpose: Startify-UIで利用可能なコンポーネント一覧を返すMCPメソッド（list_components）を実装する
target_readers: ウェブエンジニア（バックエンド、フロントエンド）
---

# Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装: list_components

---

## 1. コンポーネント定義ファイルの用意（config/components.json）

### 1.1. 目的

UIフレームワークの更新に依存しない形で、プロジェクト側で参照するコンポーネント定義を管理する。MCPはこの定義を元に一覧を返却する。

### 1.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/config/components.json` が存在する
- コンポーネントの基本情報（id/name/category/variants/description）が定義される

### 1.3. 作業

```bash
mkdir -p frontend/startify-ui-mcp/mcp-server/config
cat > frontend/startify-ui-mcp/mcp-server/config/components.json << 'EOL'
[
  {
    "id": "button",
    "name": "Button",
    "category": "forms",
    "variants": ["primary", "secondary", "link"],
    "description": "Startify-UI button component"
  },
  {
    "id": "alert",
    "name": "Alert",
    "category": "feedback",
    "variants": ["info", "success", "warning", "danger"],
    "description": "Startify-UI alert component"
  }
]
EOL
```

---

## 2. 型定義とローダーの実装（src/lib/components.ts）

### 2.1. 目的

JSON定義を読み込み、型安全に扱うローダーを用意する。

### 2.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/src/lib/components.ts` が存在する
- `loadComponents()` が `components.json` を読み込んで `Component[]` を返す

### 2.3. 作業

```bash
mkdir -p frontend/startify-ui-mcp/mcp-server/src/lib
cat > frontend/startify-ui-mcp/mcp-server/src/lib/components.ts << 'EOL'
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

export type Component = {
  id: string;
  name: string;
  category: string;
  variants?: string[];
  description?: string;
};

export function loadComponents(baseDir: string = process.cwd()): Component[] {
  const file = resolve(baseDir, 'config', 'components.json');
  const raw = readFileSync(file, 'utf-8');
  const data = JSON.parse(raw);
  if (!Array.isArray(data)) {
    throw new Error('components.json must be an array');
  }
  return data;
}
EOL
```

---

## 3. MCPツールの公開（src/server.ts）

### 3.1. 目的

`list_components` ツールをMCPサーバーに登録し、JSONの配列（`Component[]`）を返す。

### 3.2. 完了条件

- `src/server.ts` に `list_components` ツールが登録されている
- リクエストに対して `components.json` の内容が返る

### 3.3. 作業（雛形）

```bash
cat >> frontend/startify-ui-mcp/mcp-server/src/server.ts << 'EOL'
// --- list_components tool (skeleton) ---
// import { Server } from '@modelcontextprotocol/sdk/server';
// import { loadComponents } from './lib/components';
//
// // server.registerTool('list_components', {
// //   description: 'Return available Startify-UI components',
// //   inputSchema: { type: 'object', properties: {}, additionalProperties: false },
// //   async handler() {
// //     const components = loadComponents(process.cwd());
// //     return { ok: true, data: components };
// //   }
// // });
EOL
```

> 注: 実際の `Server` 初期化とツール登録は、TASK_006 以降の拡張（他メソッド連携）で整理します。ここでは実装雛形と責務分割までを対象とします。

---

## 4. 起動と検証（暫定）

### 4.1. 目的

雛形が型エラーなく読み込めること、`components.json` が正しくパースできることを確認する。

### 4.2. 完了条件

- `npm run dev` / `npm run build` が成功する（list_componentsの雛形コメントを含んだ状態）
- `loadComponents()` を一時的に呼び出してもエラーにならない（任意）

### 4.3. 作業（任意の一時確認）

```bash
# server.ts の main 内で一時確認（終わったらコメントアウト）
# import { loadComponents } from './lib/components';
# console.log('[MCP] components:', loadComponents(process.cwd()).length);
```

---

## 5. 検証項目と期待結果（テンプレ）

- 検証項目: JSON定義の整合
  - 期待: `components.json` が配列で、各要素にid/name/categoryがある
  - 実際:［ここに結果を記載］
  - 対応策: JSONスキーマの追加、CIチェックの導入

- 検証項目: ローダーの動作
  - 期待: `loadComponents()` が例外を投げずに配列を返す
  - 実際:［ここに結果を記載］
  - 対応策: 例外メッセージの改善、path解決の明示

- 検証項目: MCPツール登録の雛形
  - 期待: コメントアウトを外せば問題なくコンパイルできる（後続タスクで本番化）
  - 実際:［ここに結果を記載］
  - 対応策: SDKバージョン整合、型の明確化

---
