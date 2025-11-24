# Playground (Vite)

Startify-UIのCSS/JSを読み込み、生成HTMLを表示する最小クライアントです。E2Eの簡易検証やアクセシビリティの初期確認に使用します。

## スクリプト

```bash
npm run dev      # 開発サーバー起動
npm run build    # 本番ビルド
npm run preview  # ビルド成果物のプレビュー
```

## 使い方

1) 開発サーバーを起動しブラウザで表示します。  
2) 画面上の「サンプル表示」「サンプル（無効）表示」「クリア」を操作します。

`src/main.js` は `src/ui.js` を介して `#generated` にHTMLを挿入します。  
サンプルは `src/samples/*.html` に配置されています。

## 配信

外部配信時は `vite build` の成果物を配信します。`node_modules` 直参照は行いません。

## ライセンス

MIT（リポジトリルートの `LICENSE` を参照）。
