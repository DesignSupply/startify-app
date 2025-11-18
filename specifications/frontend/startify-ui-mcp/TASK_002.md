---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:Startify-UI（npm）導入と読み込み方針の決定
id: startify-ui-mcp_task_002
version: 1.0.0
last_updated: 2025-11-18
purpose: npm配布の @designsupply/startify-ui の導入方針と読み込み戦略を定め、playgroundで安定動作させる
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Startify-UI MCPサーバーワークスペース構築タスクリスト:Startify-UI（npm）導入と読み込み方針の決定

---

## 1. パッケージ導入確認とバージョンポリシー

### 1.1. 目的

`@designsupply/startify-ui` をnpmから導入し、バージョンポリシー（更新追従範囲）を明確化する。

### 1.2. 完了条件

- `frontend/startify-ui-mcp/mcp-server/package.json` の `dependencies` に `@designsupply/startify-ui` が含まれる  
- `frontend/startify-ui-mcp/playground/package.json` の `dependencies` に `@designsupply/startify-ui` が含まれる  
- バージョン指定は `^0.2.0`（パッチ/マイナーに追従、ブレイキングは回避）で統一されている

### 1.3. 作業

```bash
# 事前確認（任意）: 公開バージョンの確認
npm view @designsupply/startify-ui version versions
```

> メモ: 既に `package.json` に `^0.2.0` を記載済み。以降の更新方針はパッチ/マイナー追従、メジャーは手動検証。

---

## 2. 読み込み方式の決定（デフォルト: Vite バンドル）

### 2.1. 目的

playgroundで安定配信・本番運用可能な形にするため、Viteによるバンドルを標準方式とする。

### 2.2. 完了条件

- `frontend/startify-ui-mcp/playground` にViteがdev依存で導入されている
- `package.json` のscriptsに `dev/build/preview` が設定されている
- `src/main.js` で `@designsupply/startify-ui` のCSS/JSをimportしている
- `index.html` は `<script type="module" src="/src/main.js">` でエントリーを読み込む

### 2.3. 作業

```bash
cd frontend/startify-ui-mcp/playground
npm install
npm i -D vite
npx npm-add-script -k "dev" -v "vite"
npx npm-add-script -k "build" -v "vite build"
npx npm-add-script -k "preview" -v "vite preview --port 2100"

# エントリー（存在しなければ作成）
mkdir -p src
cat > src/main.js << 'EOL'
import '@designsupply/startify-ui/dist/startify-ui.min.css';
import '@designsupply/startify-ui/dist/startify-ui.min.js';

document.getElementById('generated').innerHTML = '<p>Ready.</p>';
EOL

# 起動
npm run dev
# ブラウザ: http://localhost:5173
```

> メモ: ポートは環境により異なる。固定したい場合は Vite 設定で指定。

---

## 3. 検証項目と期待結果（テンプレ用）

- 検証項目: npm依存の解決
  - 期待: `mcp-server` / `playground` ともに `node_modules/@designsupply/startify-ui` が存在
  - 実際:［ここに結果を記載］
  - 対応策: レジストリ/ネットワーク/バージョン再確認、`npm cache clean --force`

- 検証項目: playgroundの読み込み（Vite）
  - 期待: `npm run dev` が起動し、画面にスタイル/JSが適用
  - 実際:［ここに結果を記載］
  - 対応策: `src/main.js` のimportパス確認、Viteログ確認

- 検証項目: バージョンポリシー
  - 期待: `^0.2.0` でパッチ/マイナー更新時も破綻しない
  - 実際:［ここに結果を記載］
  - 対応策: 固定化（`~0.2.x` / `0.2.x`）やRenovate/Dependabot設定の検討

---
