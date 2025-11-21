---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装:list_components
id: startify-ui-mcp_task_004
version: 1.0.0
last_updated: 2025-11-18
purpose: Startify-UIで利用可能なコンポーネント一覧を返すMCPメソッド（list_components）を実装する
target_readers: ウェブエンジニア（バックエンド、フロントエンド）
---

## Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装: list_components

---

## 1. コンポーネント定義ファイルの用意（config/components.yaml）

### 1.1. 目的

UIフレームワークの更新に依存しない形で、プロジェクト側で参照するコンポーネント定義を管理する。MCPはこの定義を元に一覧を返却する。

### 1.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/config/components.yaml` が存在する
- コンポーネントの基本情報（id/name/category/description）に加え、レンダリング用メタが定義される（YAML）
  - 例: `baseClass`, `htmlElements`, `defaults.element`, `defaults.formType`, `variants.*.classNames[]`, `attributes`, `elementAttributes`, `props.disabled`, `props.formType`

### 1.3. 作業

```bash
mkdir -p frontend/startify-ui-mcp/mcp-server/config
cat > frontend/startify-ui-mcp/mcp-server/config/components.yaml << 'EOL'
- id: 'button'
  name: 'Button'
  category: 'forms'
  description: 'Startify-UI button component'
  baseClass: 'su-button'
  htmlElements: ['button', 'input', 'a']
  defaults:
    element: 'button'
    formType: 'button'
    type: 'primary'
    size: 'default'
    display: 'default'
    shape: 'rounded'
  variants:
    type:
      primary:   { classNames: ['su-button-primary'] }
      secondary: { classNames: ['su-button-secondary'] }
      fill:      { classNames: ['su-button-fill'] }
      outline:   { classNames: ['su-button-outline'] }
    size:
      small:     { classNames: ['su-button-size-small'] }
      large:     { classNames: ['su-button-size-large'] }
    display:
      block:     { classNames: ['su-button-display-block'] }
    shape:
      square:    { classNames: ['su-button-shape-square'] }
      pill:      { classNames: ['su-button-shape-pill'] }
  attributes:
    role: 'button'
  elementAttributes:
    button:
      type: 'button'
      role: 'button'
    a:
      role: 'button'
      href: '#'
      tabindex: 0
    input:
      type: 'button'
  props:
    disabled:
      classNames: ['su-button-state-disabled']
      elementAttributes:
        button:
          disabled: true
          aria-disabled: 'true'
        a:
          aria-disabled: 'true'
          tabindex: -1
        input:
          disabled: true
          aria-disabled: 'true'
    formType:
      button:
        elementAttributes:
          button: { type: 'button' }
          input:  { type: 'button' }
      submit:
        elementAttributes:
          button: { type: 'submit' }
          input:  { type: 'submit' }
      reset:
        elementAttributes:
          button: { type: 'reset' }
          input:  { type: 'reset' }
EOL
```

### 1.4. スキーマ概要と合成順

以下は `components.yaml` の型例（抜粋）。`classNames` は配列で保持する。

```ts
type VariantEntry = { classNames?: string[] };

type ComponentDefinition = {
  id: string;
  name: string;
  category: string;
  description?: string;
  baseClass?: string;                 // 例: 'su-button'
  htmlElements?: string[];            // 例: ['button','input','a']
  defaults?: {
    element?: string;                 // 例: 'button'
    formType?: 'button'|'submit'|'reset';
    type?: string;
    size?: string;
    display?: string;
    shape?: string;
  };
  variants?: Record<string, Record<string, VariantEntry>>;
  attributes?: Record<string, string|number|boolean>;
  elementAttributes?: Record<string, Record<string, string|number|boolean>>;
  props?: {
    disabled?: {
      classNames?: string[];
      elementAttributes?: Record<string, Record<string, string|number|boolean>>;
    };
    formType?: Record<'button'|'submit'|'reset', {
      elementAttributes?: Record<string, Record<string, string|number|boolean>>;
    }>;
  };
};
```

合成順（後勝ちで上書き）

1) クラス: `baseClass` → 各 `variants.*.*.classNames[]` → 真値の `props.*.classNames[]`  
2) 属性: `attributes` → `elementAttributes[element]` → 真値の `props.*.elementAttributes[element]`

> メモ: `element` は `defaults.element` を既定に選択。`formType` は `button|submit|reset` を許容し要素別に `type` を付与。`a` は既定で `href: '#'`, `tabindex: 0` を付与し、`disabled` 時は `aria-disabled: 'true'` と `tabindex: -1` を適用。

---

## 2. 型定義とローダーの実装（src/lib/components.ts）

### 2.1. 目的

YAML定義を読み込み、型安全に扱うローダーを用意する。

### 2.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/src/lib/components.ts` が存在する
- `loadComponents()` が `components.yaml` を読み込んで `Component[]` を返す

### 2.3. 作業

```bash
mkdir -p frontend/startify-ui-mcp/mcp-server/src/lib
cat > frontend/startify-ui-mcp/mcp-server/src/lib/components.ts << 'EOL'
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { parse } from 'yaml';

export type Component = {
  id: string;
  name: string;
  category: string;
  variants?: string[];
  description?: string;
};

export function loadComponents(baseDir: string = process.cwd()): Component[] {
  const file = resolve(baseDir, 'config', 'components.yaml');
  const raw = readFileSync(file, 'utf-8');
  const data = parse(raw);
  if (!Array.isArray(data)) {
    throw new Error('components.yaml must be an array');
  }
  return data as Component[];
}
EOL
```

---

## 3. MCPツールの公開（src/server.ts）

### 3.1. 目的

`list_components` ツールをMCPサーバーに登録し、配列（`Component[]`）を返す。

### 3.2. 完了条件

- `src/server.ts` に `list_components` ツールが登録されている（雛形）
- リクエストに対して `components.yaml` の内容が返る（将来の本配線時）

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

雛形が型エラーなく読み込めること、`components.yaml` が正しくパースできることを確認する。

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

- 検証項目: YAML定義の整合
  - 期待: `components.yaml` が配列で、各要素にid/name/categoryがある
  - 実際:［ここに結果を記載］
  - 対応策: スキーマの追加、CIチェックの導入

- 検証項目: ローダーの動作
  - 期待: `loadComponents()` が例外を投げずに配列を返す
  - 実際:［ここに結果を記載］
  - 対応策: 例外メッセージの改善、path解決の明示

- 検証項目: MCPツール登録の雛形
  - 期待: コメントアウトを外せば問題なくコンパイルできる（後続タスクで本番化）
  - 実際:［ここに結果を記載］
  - 対応策: SDKバージョン整合、型の明確化

---
