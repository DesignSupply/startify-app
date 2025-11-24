---
title: Startify-UI MCPサーバーワークスペース構築タスクリスト:動作検証（E2Eシナリオ・アクセシビリティ基本確認）
id: startify-ui-mcp_task_009
version: 1.0.0
last_updated: 2025-11-24
purpose: PlaygroundとMCPサーバーを用いたエンドツーエンド基本検証と、Startify-UI適用要素のアクセシビリティ基礎確認を行う
target_readers: ウェブエンジニア（フロントエンド/バックエンド）、UIデザイナー
---

## Startify-UI MCPサーバーワークスペース構築タスクリスト: 動作検証（E2E/アクセシビリティ）

---

## 1. 目的と完了条件

### 1.1. 目的

- 現時点の機能（Playgroundでの表示、コンポーネント定義読込、ログ出力）を、手順通りに動かして検証可能な状態にする。
- Startify-UI適用の基本的な見た目と、役割/状態のアクセシビリティ最小要件（role/aria-*）をチェックする。

### 1.2. 完了条件

- Playgroundでサンプル表示/クリアが動作する。
- MCPサーバーで `components.yaml` の読込が成功し、ログに件数が表示される（例: `components count: 1`）。
- Button要素のrole/状態（`role="button"`, disabled時の `aria-disabled`/`tabindex`）の基本が満たされている。
- ドキュメント末尾のチェックリストをすべて満たす。

---

## 2. 前提

- Node.js: ^22.11.0 / npm: ^10.8.2
- 依存・ソースは既存タスクの手順通りにセットアップ済み（TASK_001〜TASK_008）。
- `.env.local` は `frontend/startify-ui-mcp/.env.local`（任意、無くても可）
  - 例: `STARTIFY_LOG_LEVEL=debug`
  - 例: `STARTIFY_COMPONENTS_FILE=config/components.yaml`

---

## 3. E2Eシナリオ（手動）

### 3.1. MCPサーバー起動（開発）

```bash
cd frontend/startify-ui-mcp/mcp-server
npm run dev
```

期待結果:

- ログに `Startify-UI MCP server bootstrap ready.` が出力される。
- `STARTIFY_LOG_LEVEL=debug` の場合、`entry: ...` のデバッグ行が表示される。
- `components count: N` がinfoで表示される（`components.yaml` の定義数に一致）。

### 3.2. Playground起動（開発）

```bash
cd frontend/startify-ui-mcp/playground
npm run dev
```

期待結果:

- ブラウザ表示で初期文言 `Ready.` が出る。
- 「サンプル表示」クリックで `button.html` の内容が `#generated` に挿入される。
- 「クリア」クリックで `#generated` が空になる。
- Startify-UIの見た目が反映される（ボタンのスタイル等）。

---

## 4. アクセシビリティ基本確認（最小）

> 対象: Button（`components.yaml` の `button` 定義、Playgroundサンプル）

- 役割:
  - `role="button"` が付与されているか（`a` 要素のボタン表現時など）。
- 状態:
  - disabled相当のとき `aria-disabled="true"` が付与されているか。
  - `a` 要素がdisabled相当なら `tabindex="-1"` でフォーカス抑止されているか。
- 入力支援:
  - ボタンにフォーカスリングが表示され、キーボード操作でアクティブにできるか。

> メモ: 本段階ではスクリーンリーダー実機検証は範囲外。後続拡張でテスト観点を追加する。

---

## 5. 追加検証（任意）

- `.env.local` のログレベル切替
  - `STARTIFY_LOG_LEVEL=info` → debug行が出ないこと。
- `STARTIFY_COMPONENTS_FILE` の切替
  - 絶対/相対パスの双方で `components count: N` の出力が正しく変化すること。
- サンプルHTML拡張
  - `src/samples/` に別HTMLを用意して差し替え表示できること。

---

## 6. 検証項目と期待結果

- 検証項目: MCPサーバー起動
  - 期待: 起動ログが出力され、`components count: N` が表示される
  - 実際:［ここに結果を記載］
  - 対応策: `STARTIFY_COMPONENTS_FILE` のパス確認、権限/存在チェック

- 検証項目: Playgroundの操作
  - 期待:「サンプル表示」「クリア」が動作する
  - 実際:［ここに結果を記載］
  - 対応策: fetchパス/キャッシュ、イベント紐付け確認

- 検証項目: アクセシビリティ（最小）
  - 期待: `role="button"`, `aria-disabled`/`tabindex` の付与が状況に合致
  - 実際:［ここに結果を記載］
  - 対応策: `components.yaml` の `attributes`/`elementAttributes`/`props` を見直し

---

## 7. トラブルシュート

- ENOENT（components.yamlが見つからない）
  - 原因: `STARTIFY_COMPONENTS_FILE` の相対パスに `mcp-server/` を含めた二重指定など
  - 対応: 相対は `config/components.yaml`、既定に戻す場合は環境変数未設定でOK
- ポート起動不可（Playground）
  - 対応: Viteのポート変更（`vite --port 5174` など）または他プロセス停止
- debugログが出ない
  - 対応: `.env.local` の `STARTIFY_LOG_LEVEL=debug` を確認、`npm run dev` の前に有効化

---

## 8. 注意点・今後の拡張

- MCPの `tools/call` 本配線後、`generate_page` の戻りHTMLをPlaygroundへ反映するE2Eに拡張する。
- 自動化（Playwright等）は範囲外。将来のCI導入時に別タスクで定義する。
- a11y観点の追加（キーボードトラップ、コントラスト、ラベル関連）は後続タスクで強化。

---
