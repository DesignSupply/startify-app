---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装:generate_page
id: startify-ui-mcp_task_006
version: 1.0.0
last_updated: 2025-11-21
purpose: Startify-UIのユーティリティクラスを適用したHTML文字列を生成するMCPメソッド generate_page を設計・実装する
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

## Startify-UI MCPサーバーワークスペース構築タスクリスト:MCPメソッド実装:generate_page

---

## 1. 目的と完了条件

### 1.1. 目的

- MCPツール `generate_page` を追加し、指定されたコンポーネント群とバリアント／テキスト等の入力に基づき、Startify-UIのクラスを適用した最小HTMLを生成して返す。
- デザイントークン（YAML）を必要に応じて参照可能にする（色・余白・タイポグラフィ等の拡張余地を確保）。

### 1.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/src/lib/generator.ts` が存在すること
- `generatePage()` が入力（コンポーネント配列）から有効なHTML文字列を返すこと
- `frontend/startify-ui-mcp/mcp-server/src/server.ts` に `generate_page` のコメント雛形を追記（initialize/tools/call連携は後続で本配線）
- NodeNext + ESMに準拠（相対インポートは `.js` 拡張子で記述）
- `npm run build` が成功すること

---

## 2. 仕様（I/Oと基本ルール）

### 2.1. 入力（例）

```json
{
  "title": "サンプルページ",
  "components": [
    { "id": "button", "options": { "type": "primary" }, "text": "プライマリーボタン" }
  ]
}
```

### 2.2. 出力

- HTML文字列（例）

```html
<h1>サンプルページ</h1>
<button class="su-button-primary" type="button" role="button">プライマリーボタン</button>
```

### 2.3. 基本ルール

- 未知の `id` はスキップ（後続でエラー化のオプション追加を検討）
- `variant` が省略された場合は各コンポーネントの推奨既定値を使用
- テキストはHTMLエスケープして挿入
- 将来的に `props` を追加し、属性／補助的クラス付与を拡張可能にする

### 2.4. 参照スキーマと合成順

`generatePage()` は `config/components.yaml` のスキーマを参照してクラスと属性を合成する。

- 要素選択: `defaults.element` を既定に採用（例: `'button'`）。`a` を選ぶ場合は既定で `href: '#'`, `tabindex: 0` を適用。
- type属性: `defaults.formType` および `props.formType` により `button|submit|reset` を要素別に付与。
- disabled時の挙動: `props.disabled` の `classNames` を付与。属性は要素別に適用（例: `button/input` は `disabled: true, aria-disabled: 'true'`、`a` は `aria-disabled: 'true', tabindex: -1`）。

合成順（後勝ち）

1) クラス: `baseClass` → 各 `variants.*.*.classNames[]` → 真値の `props.*.classNames[]`  
2) 属性: `attributes` → `elementAttributes[element]` → 真値の `props.*.elementAttributes[element]`

---

## 3. 実装（generatorユーティリティ）

### 3.1. ファイル作成

- パス: `frontend/startify-ui-mcp/mcp-server/src/lib/generator.ts`

```bash
cat > frontend/startify-ui-mcp/mcp-server/src/lib/generator.ts << 'EOL'
import type { DesignTokens } from './tokens.js';
import { loadComponents } from './components.js';

export type GenerateComponentInput = {
  id: string;
  element?: string;
  options?: Record<string, string>;
  text?: string;
  props?: Record<string, unknown>; // 例: { disabled: true, formType: 'submit' }
};

export type GeneratePageInput = {
  title?: string;
  components: GenerateComponentInput[];
};

export function generatePage(input: GeneratePageInput, tokens: DesignTokens): string {
  const parts: string[] = [];
  const defs = loadComponents(process.cwd()) as any[];

  if (input.title) {
    parts.push(`<h1>\${escapeHtml(String(input.title))}</h1>`);
  }

  for (const c of input.components ?? []) {
    const def = defs.find((d) => d.id === c.id);
    if (!def) continue;

    // element 決定
    const element = c.element ?? def?.defaults?.element ?? 'div';

    // クラス合成
    const classNames: string[] = [];
    if (def.baseClass) classNames.push(def.baseClass);
    const options = c.options ?? {};
    for (const [axis, value] of Object.entries(options)) {
      const entry = def?.variants?.[axis]?.[value];
      if (entry?.classNames) classNames.push(...entry.classNames);
    }
    if (c.props?.['disabled'] && def?.props?.disabled?.classNames) {
      classNames.push(...def.props.disabled.classNames);
    }

    // 属性合成（後勝ち）
    const attrs: Record<string, string | number | boolean> = {};
    Object.assign(attrs, def.attributes ?? {});
    Object.assign(attrs, def.elementAttributes?.[element] ?? {});
    if (c.props?.['disabled'] && def?.props?.disabled?.elementAttributes?.[element]) {
      Object.assign(attrs, def.props.disabled.elementAttributes[element]);
    }
    const formType = String((c.props as any)?.formType ?? def?.defaults?.formType ?? '');
    if (formType && def?.props?.formType?.[formType]?.elementAttributes?.[element]) {
      Object.assign(attrs, def.props.formType[formType].elementAttributes[element]);
    }

    // テキスト
    const label = String(c.text ?? '');

    parts.push(\`<\${element} \${renderAttrs(attrs)} class="\${classNames.join(' ').trim()}">\${escapeHtml(label)}</\${element}>\`);
  }

  return parts.join('\\n');
}

function renderAttrs(attrs: Record<string, string | number | boolean>): string {
  const pairs: string[] = [];
  for (const [k, v] of Object.entries(attrs)) {
    if (typeof v === 'boolean') {
      if (v) pairs.push(k); // trueなら属性名のみ
    } else {
      pairs.push(\`\${k}="\${String(v)}"\`);
    }
  }
  return pairs.join(' ');
}

function escapeHtml(s: string): string {
  return s.replace(/[&<>"']/g, (ch) => (
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } as const)[ch]!
  ));
}
EOL
```

> メモ: `tokens` は現状未使用だが、将来的にクラスの出し分けやインラインCSSの生成に活用するため引数として受け取る。

---

## 4. MCP側の雛形配線（server.ts）

### 4.1. 目的

- `generate_page` ツールのコメント雛形を追記（後続のinitialize/tools/callワイヤリングで本配線）。

### 4.2. 作業

```bash
cat >> frontend/startify-ui-mcp/mcp-server/src/server.ts << 'EOL'
// --- generate_page tool (skeleton) ---
// import { loadDesignTokens } from './lib/tokens.js';
// import { generatePage } from './lib/generator.js';
// // server.on('tools/call', async ({ name, arguments: args }) => {
// //   if (name === 'generate_page') {
// //     const tokens = loadDesignTokens(process.cwd());
// //     const html = generatePage(args as any, tokens);
// //     return { ok: true, data: { html } };
// //   }
// // });
EOL
```

> 注: MCP SDKは `registerTool` を直接持たないため、`initialize` でツール宣言→ `tools/call` ハンドラで分岐する方式を後続で適用する。

---

## 5. 動作検証

### 5.1. ビルド

```bash
cd frontend/startify-ui-mcp/mcp-server
npm run build
```

期待結果: 成功終了（エラーなし）。

### 5.2. 最小単体確認（任意）

`server.ts` の `main` 内に一時コードを入れて、`generatePage()` の戻り値をログ出力しても良い。

```ts
// import { loadDesignTokens } from './lib/tokens.js';
// import { generatePage } from './lib/generator.js';
// const html = generatePage({ title: 'Demo', components: [{ id: 'button', variant: 'primary', text: 'OK' }] }, loadDesignTokens(process.cwd()));
// console.log('[MCP] generated html:\\n', html);
```

検証後は削除すること。

---

## 6. 検証項目と期待結果

- 検証項目: 生成HTMLの妥当性
  - 期待: `button`, `alert` 等の既定サンプルがStartify-UIのクラスを付与して出力される
  - 実際:［ここに結果を記載］
  - 対応策: マッピング拡張、未知IDの扱いをエラー化等

- 検証項目: ESMインポート
  - 期待: 相対パスは `.js` 指定でエラーにならない
  - 実際:［ここに結果を記載］
  - 対応策: import文の拡張子修正、ビルド後の出力構成確認

- 検証項目: トークン参照
  - 期待: `loadDesignTokens()` で `/frontend/startify-ui-mcp/design-tokens` が解決可能（`design-tokens`/`../design-tokens`）
  - 実際:［ここに結果を記載］
  - 対応策: CWDの見直し、環境変数 `TOKENS_DIR` 導入検討

---

## 7. 注意点・今後の拡張

- 現状は最小マッピング（`button` , `alert`）。`components.yaml` と連動し、マッピングを自動生成する設計を後続で検討。
- アクセシビリティ: `role`, `aria-*` の追加やキーボードフォーカス等は今後の拡張で整備。
- セキュリティ: テキスト挿入は必ずHTMLエスケープを通すこと。
- 出力レイアウト: ラッパー（`<main>` , コンテナクラス等）やグリッドは段階的に拡張。
