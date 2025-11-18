---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:ワークスペース初期化
id: startify-ui-mcp_task_001
version: 1.0.0
last_updated: 2025-11-18
purpose: /frontend/startify-ui-mcp の初期化と基礎設定（Node.js製MCP + Startify-UI npm）
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# Startify-UI MCPサーバーワークスペース構築タスクリスト:ワークスペース初期化

Startify-UI（npm配布）とMCPサーバー（Node.js + TypeScript）を用いた、UI自動生成の実験用ワークスペースを `/frontend/startify-ui-mcp` に初期構築します。  
※ Next/Astro等の既存プロジェクトを汚染しない独立環境として扱います。

---

## 参考

- Startify-UI公式: `https://lab.designsupply-web.com/startify-ui/`
- Startify-UI GitHub: `https://github.com/DesignSupply/startify-ui`

---

## 前提

- Node.js: ^22.11.0 / npm: ^10.8.2（プロジェクト技術スタックに準拠）
- リポジトリルート（`/`）で作業すること
- Docker等のサーバー環境は本タスクでは不要（ローカルで完結）

---

## 1. ディレクトリの作成

### 1.1. 目的

`/frontend/startify-ui-mcp` 配下に、MCPサーバーとPlaygroundの基本ディレクトリを作成し、以降の実装作業を安全に開始できる状態にする。

### 1.2. 完了条件

以下を満たすこと：
- `frontend/startify-ui-mcp/mcp-server/src` が存在する
- `frontend/startify-ui-mcp/playground` が存在する

### 1.3. ディレクトリの作成

- パス: `/frontend/startify-ui-mcp/`

```bash
mkdir -p frontend/startify-ui-mcp/mcp-server/src
mkdir -p frontend/startify-ui-mcp/playground
```

### 1.4. .gitignore（ワークスペーススコープ）の作成

#### 1.4.1. 目的

`/frontend/startify-ui-mcp` 配下のみで `node_modules/` やビルド成果物をGit追跡から除外し、他のプロジェクト（Next/Astro等）への影響を避ける。

#### 1.4.2. 完了条件

- `frontend/startify-ui-mcp/.gitignore` が作成されている
- 以下の主要項目が含まれている（例）
  - `node_modules/`, `dist/`, `build/`, `.vite/`, `.astro/`, `.next/`, `out/`
  - `*.tsbuildinfo`, `.env*`, `.DS_Store`, `.vscode/`, `.idea/`, 各種npm/yarn/pnpmログ

#### 1.4.3. 作業

```bash
cat > frontend/startify-ui-mcp/.gitignore << 'EOL'
# Project-scoped ignores for frontend/startify-ui-mcp

# OS/Editor
.DS_Store
.idea/
.vscode/
*.swp

# Dependencies
node_modules/
npm-debug.log*
yarn-debug.log*
yarn-error.log*
pnpm-debug.log*
.pnpm-store/

# Build outputs and caches
dist/
build/
.vite/
.astro/
.next/
out/

# TypeScript
*.tsbuildinfo

# Env files (keep examples tracked elsewhere)
.env
.env.local
.env.development.local
.env.production.local
.env.test.local
EOL
```

---

## 2. mcp-server プロジェクト初期化（Node.js + TypeScript）

### 2.1. 目的

TypeScriptでMCPサーバーを実装するための最小構成を用意し、依存（`@designsupply/startify-ui`, `@modelcontextprotocol/sdk`）とビルド/起動スクリプトを整える。

### 2.2. 完了条件

以下を満たすこと：
- `package.json` が作成され、`dependencies` に `@designsupply/startify-ui`, `@modelcontextprotocol/sdk` が入っている
- `devDependencies` に `typescript`, `ts-node`, `@types/node` が入っている
- `tsconfig.json` が作成され、`outDir` が `dist`、`rootDir` が `src` に設定されている

### 2.3. 初期化コマンド

- パス: `/frontend/startify-ui-mcp/mcp-server/`

```bash
cd frontend/startify-ui-mcp/mcp-server
npm init -y

# ランタイム依存
npm i @designsupply/startify-ui @modelcontextprotocol/sdk

# 開発依存
npm i -D typescript ts-node @types/node

# TypeScript 初期化
npx tsc --init
```

### 2.4. tsconfig.json の推奨設定

```bash
cat > tsconfig.json << 'EOL'
{
  "compilerOptions": {
    "target": "ES2022",
    "module": "NodeNext",
    "moduleResolution": "NodeNext",
    "strict": true,
    "esModuleInterop": true,
    "forceConsistentCasingInFileNames": true,
    "skipLibCheck": true,
    "outDir": "dist",
    "rootDir": "src"
  },
  "include": ["src"]
}
EOL
```

> メモ: `"moduleResolution": "NodeNext"` を使う場合、`"module"` も `"NodeNext"` に合わせる必要があります。

### 2.5. package.json スクリプトの追加（雛形）

```bash
npx npm-add-script -k "build"  -v "tsc"
npx npm-add-script -k "start"  -v "node dist/server.js"
npx npm-add-script -k "dev"    -v "node --loader ts-node/esm src/server.ts"
```

> 注: `src/index.ts` の実装は後続タスク（TASK_003 以降）で行います。

---

## 3. playground プロジェクト初期化（最小クライアント）

### 3.1. 目的

生成HTML（Startify-UIクラス適用）を挿入して挙動確認できる最小の受け皿を用意する。配信/本番運用を見据え、Viteによるバンドルを前提とする。

### 3.2. 完了条件

以下を満たすこと：
- `package.json` が作成され、`dependencies` に `@designsupply/startify-ui` が入っている
- Viteをdev依存に導入し、`npm run dev` で開発サーバーが起動する
- `src/main.js` から `@designsupply/startify-ui` のCSS/JSをimportして読み込める
- `index.html` はViteのエントリー（`/src/main.js`）を参照している

### 3.3. 初期化コマンド

- パス: `/frontend/startify-ui-mcp/playground/`

```bash
cd ../playground
npm init -y
npm i @designsupply/startify-ui
npm i -D vite
```

#### 3.3.1. package.json スクリプトの追加

```bash
npx npm-add-script -k "dev" -v "vite"
npx npm-add-script -k "build" -v "vite build"
npx npm-add-script -k "preview" -v "vite preview --port 2100"
```

#### 3.3.2. エントリーファイルの作成（src/main.js）

```bash
mkdir -p src
cat > src/main.js << 'EOL'
import '@designsupply/startify-ui/dist/startify-ui.min.css';
import '@designsupply/startify-ui/dist/startify-ui.min.js';

document.getElementById('generated').innerHTML = '<p>Ready.</p>';
EOL
```

### 3.4. 最小 HTML の雛形作成

```bash
cat > index.html << 'EOL'
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Startify-UI MCP Playground</title>
    <link rel="icon" type="image/svg+xml" href="data:," />
  </head>
  <body>
    <div id="generated"></div>
    <!-- Vite エントリー -->
    <script type="module" src="/src/main.js"></script>
  </body>
</html>
EOL
```

> 注: 外部デプロイ時は `vite build` の成果物を配信します。`node_modules` 直参照は使用しません。

---

## 4. 動作検証（初期セットアップ）

### 4.1 前提ツールの確認

```bash
node -v
npm -v
```

期待結果: Node `v22.11.x` / npm `v10.8.x` が表示される。

### 4.2 ディレクトリ/設定ファイルの確認

```bash
ls -la frontend/startify-ui-mcp
ls -la frontend/startify-ui-mcp/mcp-server
ls -la frontend/startify-ui-mcp/playground
```

期待結果:
- `mcp-server/` に `package.json`, `tsconfig.json`, `src/` が存在
- `playground/` に `package.json`, `index.html` が存在

### 4.3 TypeScript の動作確認（mcp-server）

```bash
cd frontend/startify-ui-mcp/mcp-server
npx tsc --version
```

期待結果: TypeScriptのバージョンが表示される（例: `Version 5.x.x`）。

---

## 5. 検証項目と期待結果（テンプレ用）

- 検証項目: 依存パッケージの導入
  - 期待: `@designsupply/startify-ui`, `@modelcontextprotocol/sdk` が `dependencies` に存在
  - 実際:［ここに結果を記載］
  - 対応策: パッケージ再インストール、ネットワーク/レジストリ確認

- 検証項目: TypeScript設定
  - 期待: `tsconfig.json` が推奨値で生成、`npx tsc --version` が成功
  - 実際:［ここに結果を記載］
  - 対応策: `tsconfig.json` 再生成、バージョン互換性の確認

- 検証項目: Playgroundの基本表示
  - 期待: `index.html` でStartify-UIのスタイル/JSが読み込まれる
  - 実際:［ここに結果を記載］
  - 対応策: パス修正、ビルド/配信方法の見直し

---
