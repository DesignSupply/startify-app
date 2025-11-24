# MCP Server (Node + TypeScript)

Startify-UI向けのMCPサーバーです。ESM/NodeNextで実装し、コンポーネント定義やデザイントークンを参照して将来のツール呼び出し（tools/call）に備えます。

## スクリプト

```bash
npm run dev    # ESM + ts-node（.env.local を --env-file で読み込み）
npm run build  # TypeScriptビルド（出力: dist）
npm run start  # ビルド成果物を起動（.env.local を --env-file で読み込み）
```

## 環境変数（.env.local）

`.env.local` はワークスペース直下（`frontend/startify-ui-mcp/.env.local`）に配置します。`package.json` のスクリプトで `--env-file=../.env.local` を指定しています。

- `STARTIFY_LOG_LEVEL`: `debug` | `info` | `warn` | `error`（既定: `info`）
- `STARTIFY_COMPONENTS_FILE`: コンポーネント定義のパス  
  - 例（相対推奨）: `config/components.yaml`  
  - 絶対/相対どちらも可。相対はカレント（mcp-server）基準。
- `STARTIFY_TOKENS_DIR`: デザイントークンのディレクトリ（省略時は自動解決に委譲）

## 主要ファイル

- `src/server.ts`: エントリーポイント
- `src/lib/logger.ts`: ロガー（`STARTIFY_LOG_LEVEL` に応じたレベル制御）
- `src/lib/errors.ts`: 例外整形（`validation` / `internal` / `dependency`）
- `src/lib/components.ts`: コンポーネント定義ローダー（YAML）
- `src/lib/tokens.ts`: デザイントークンローダー（YAML）
- `src/lib/generator.ts`: HTML生成ユーティリティ（将来のMCPツールに連携）
- `config/components.yaml`: コンポーネント定義（例: button）

## ライセンス

MIT（リポジトリルートの `LICENSE` を参照）。
