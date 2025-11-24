# Startify-UI MCP Workspace

AI駆動開発（AIDD）向けの実験用ワークスペースです。Startify-UI（npm配布）とMCPサーバー（Node.js + TypeScript）を組み合わせ、UI生成やプレビュー検証を行います。

## 構成

```
frontend/startify-ui-mcp/
├─ mcp-server/         # MCPサーバー（Node + TS, ESM/NodeNext）
├─ playground/         # 最小プレビュークライアント（Vite）
└─ design-tokens/      # デザイントークン（YAML）
```

## 必要要件

- Node.js: ^22.11.0
- npm: ^10.8.2

## クイックスタート

1) 依存インストール（初回）

```bash
cd frontend/startify-ui-mcp/mcp-server && npm i
cd ../playground && npm i
```

2) MCPサーバー（開発）

```bash
cd frontend/startify-ui-mcp/mcp-server
npm run dev
```

3) Playground（開発）

```bash
cd frontend/startify-ui-mcp/playground
npm run dev
```

## 環境変数（.env.local）

`.env.local` はワークスペース直下に配置します（パス: `frontend/startify-ui-mcp/.env.local`）。MCPサーバーの起動スクリプトは `--env-file=../.env.local` により読み込みます。

例:

```
STARTIFY_LOG_LEVEL=debug
STARTIFY_COMPONENTS_FILE=config/components.yaml
# STARTIFY_TOKENS_DIR=frontend/startify-ui-mcp/design-tokens
```

## ライセンス

MIT（リポジトリルートの `LICENSE` を参照）。主要依存の表記は `CREDITS.md` を参照してください。
