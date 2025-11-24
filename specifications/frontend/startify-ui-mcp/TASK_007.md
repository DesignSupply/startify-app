---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:Playground（最小Webクライアント）
id: startify-ui-mcp_task_007
version: 1.0.0
last_updated: 2025-11-24
purpose: Viteベースの最小Webクライアント（Playground）を整備し、Startify-UIのCSS/JSを読み込んだ上で、生成HTMLを安全に表示・検証できる受け皿を用意する
target_readers: ウェブエンジニア（フロントエンド）、UIデザイナー
---

## Startify-UI MCPサーバーワークスペース構築タスクリスト:Playground（最小Webクライアント）

---

## 1. 目的と完了条件

### 1.1. 目的

- Startify-UI（npm配布）のCSS/JSをVite経由で読み込み、生成HTMLをブラウザでプレビューできる最小環境を用意する。
- 将来のMCP連携（tools/callで `generate_page` を呼び出して得たHTMLを描画）の受け皿として、表示・リセット・サンプル読み込み等の最小UI操作を整える。
- セキュアなプレビュー（HTMLエスケープや最小限のDOM操作）と、検証作業を効率化するための簡素なハンドラーを用意する。

### 1.2. 完了条件

- `frontend/startify-ui-mcp/playground/` に以下が存在し、`npm run dev` が起動すること
  - `index.html` が `/src/main.js` をViteエントリーとして参照している（既存維持）
  - `src/main.js` がStartify-UIのCSS/JSをimportしている（既存維持）
  - `src/ui.js` を新設し、表示領域の更新・クリア・サンプル読込の最低限の関数を提供
  - `src/samples/` にプレレンダーされた最小HTMLサンプル（例: `button.html`）を配置
- ブラウザで「サンプル表示」「クリア」の操作ができ、Startify-UIのスタイル/JSが効いていることが目視確認できる。
- 将来のMCPブリッジ導入（別タスク）により、取得HTMLをそのまま差し替え表示できる構成になっている（関数の責務が分離されている）。

---

## 2. 構成方針

- ビルド/配信はViteを使用（`node_modules` 直参照は禁止）。  
- 最小限のDOM操作に限定し、プレーンJSで実装（TypeScript導入は不要）。  
- Playgroundは「生成済みHTMLを描画する」責務に専念し、HTML生成ロジックはMCPサーバー側に委譲（将来のMCP連携で置換）。  
- UIの見た目はStartify-UIのデフォルトスタイル適用のみ。Playground独自デザインは行わない。

---

## 3. 追加/更新ファイル

- 追加: `frontend/startify-ui-mcp/playground/src/ui.js`
- 追加: `frontend/startify-ui-mcp/playground/src/samples/button.html`
- 更新（必要なら）: `frontend/startify-ui-mcp/playground/src/main.js`（操作ボタンのイベント紐付けを追加）
- 既存維持: `frontend/startify-ui-mcp/playground/index.html`（Viteエントリーのまま）

---

## 4. 実装手順

> 作業パス: `/frontend/startify-ui-mcp/playground`

### 4.1. サンプルHTMLの配置（プレレンダー）

```bash
mkdir -p src/samples
cat > src/samples/button.html << 'EOL'
<h1>サンプルページ</h1>
<button class="su-button-primary" type="button" role="button">プライマリーボタン</button>
EOL
```

### 4.2. UIユーティリティの追加（src/ui.js）

```bash
cat > src/ui.js << 'EOL'
export function setGeneratedHtml(html) {
  const el = document.getElementById('generated');
  if (!el) return;
  el.innerHTML = html;
}

export function clearGenerated() {
  const el = document.getElementById('generated');
  if (!el) return;
  el.innerHTML = '';
}

export async function loadSampleHtml(path) {
  const res = await fetch(path, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Failed to load sample: ${res.status}`);
  return await res.text();
}
EOL
```

### 4.3. main.js の拡張（イベント紐付け）

> 既存の Startify-UI import を残したまま、操作ボタンへのイベントを追加します。

```bash
applypatch << 'EOPATCH'
*** Begin Patch
*** Update File: frontend/startify-ui-mcp/playground/src/main.js
@@
 import '@designsupply/startify-ui/dist/startify-ui.min.css';
 import '@designsupply/startify-ui/dist/startify-ui.min.js';
 
-document.getElementById('generated').innerHTML = '<p>Ready.</p>';
+import { setGeneratedHtml, clearGenerated, loadSampleHtml } from './ui.js';
+
+const ready = document.getElementById('generated');
+if (ready) {
+  ready.innerHTML = '<p>Ready.</p>';
+}
+
+const showBtn = document.getElementById('action-show-sample');
+if (showBtn) {
+  showBtn.addEventListener('click', async () => {
+    try {
+      const html = await loadSampleHtml('/src/samples/button.html');
+      setGeneratedHtml(html);
+    } catch (e) {
+      console.error(e);
+      alert('サンプル読み込みに失敗しました。');
+    }
+  });
+}
+
+const clearBtn = document.getElementById('action-clear');
+if (clearBtn) {
+  clearBtn.addEventListener('click', () => {
+    clearGenerated();
+  });
+}
*** End Patch
EOPATCH
```

> 補足: `applypatch` は擬似コマンド表記です。実際の変更は本タスクの「実装時」にエディタで反映してください（この文書は手順書です）。

### 4.4. index.html の最小操作UI（必要に応じて）

> 既存の雛形を保ちつつ、操作ボタンを body 末尾に追加します。Viteのエントリー指定は維持します。

```html
<!-- 参考差分（概念図） -->
<body>
  <div style="margin: 1rem 0;">
    <button id="action-show-sample" class="su-button-primary" type="button" role="button">サンプル表示</button>
    <button id="action-clear" class="su-button-secondary" type="button" role="button">クリア</button>
  </div>
  <div id="generated"></div>
  <script type="module" src="/src/main.js"></script>
  <!-- 既存のViteエントリー行は維持 -->
</body>
```

> 注: UIはPlayground内部の補助用途に限定し、独自の装飾は行いません。Startify-UIの基本クラスのみを適用します。

---

## 5. 動作確認

### 5.1. 開発サーバー起動

```bash
cd frontend/startify-ui-mcp/playground
npm run dev
```

ブラウザで表示し、以下を確認する。

- ページ初期表示で `Ready.` が表示される
- 「サンプル表示」クリックで `button.html` の内容が `#generated` に描画される
- 「クリア」クリックで `#generated` が空になる
- Startify-UIのスタイル/挙動が有効（ボタンの見た目・フォーカス・押下時の挙動）

---

## 6. 検証項目と期待結果

- 検証項目: Startify-UIの読み込み
  - 期待: CSS/JSが正常に読み込まれ、コンソールエラーがない
  - 実際:［ここに結果を記載］
  - 対応策: importパスの確認、`node_modules` の再インストール、Viteのキャッシュ削除

- 検証項目: サンプルHTMLの描画
  - 期待: `button.html` が `#generated` に正しく挿入される
  - 実際:［ここに結果を記載］
  - 対応策: fetchパスやViteのベースパスの見直し

- 検証項目: クリア動作
  - 期待:「クリア」クリックで表示領域が空になる
  - 実際:［ここに結果を記載］
  - 対応策: イベント紐付け確認、DOM要素IDの一致確認

---

## 7. 注意点・今後の拡張

- 現状はプレレンダーHTMLを描画するのみ。後続タスクでMCPブリッジ（initializeとtools/call配線）を追加し、`generate_page` の戻りHTMLをそのまま `setGeneratedHtml()` に渡す構成へ拡張する。
- デザイントークンの可視化（一覧表示や検索）は後続拡張。現段階では不要。
- セキュリティ: 任意文字列の生HTML挿入は信頼済み出力のみを対象とし、MCPからの生成物はサニタイズ/エスケープポリシーを検討する。

---
